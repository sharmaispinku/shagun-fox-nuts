<?php
/**
 * POST /api/orders.php — public quote-request endpoint.
 *
 * Accepts a normal form post (redirects on non-JS) or JSON (fetch) and returns
 * { ok, reference, message, errors } for the front-end.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!is_post()) {
    json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

// Feature switch from config.php.
if (empty(CONFIG['features']['online_orders'])) {
    json_response(['ok' => false, 'message' => 'Online requests are temporarily paused. Please call ' . SITE_PHONE . '.'], 503);
}

$input = require_csrf();

$wantsJson = wants_json();

/* --------------------------------------------------------------- honeypot */
if (trim((string)($input['website'] ?? '')) !== '') {
    json_response([
        'ok'        => true,
        'reference' => 'SHG-' . date('ymd') . '-AUTO',
        'message'   => 'Request received.',
    ]);
}

/* ------------------------------------------------------------- rate limit */
$ip      = client_ip();
$bucket  = 'order:' . hash('sha256', $ip);
$limit   = (int)(CONFIG['security']['rate_limit_per_hour'] ?? 6);

if (rate_limited($bucket, $limit, 3600)) {
    json_response([
        'ok'      => false,
        'message' => 'Too many requests from this connection. Please try again in an hour, or WhatsApp us on ' . SITE_PHONE . '.',
    ], 429);
}

/* --------------------------------------------------------------- validate */
$types   = order_types();
$gradeRows = grade_options();

$name     = trim((string)($input['name'] ?? ''));
$business = trim((string)($input['business_name'] ?? ''));
$phoneRaw = trim((string)($input['phone'] ?? ''));
$email    = trim((string)($input['email'] ?? ''));
$city     = trim((string)($input['city'] ?? ''));
$state    = trim((string)($input['state'] ?? ''));
$grade    = trim((string)($input['grade'] ?? ''));
$qty      = (float)str_replace(',', '', (string)($input['quantity_kg'] ?? '0'));
$pack     = trim((string)($input['packaging'] ?? ''));
$timeline = trim((string)($input['timeline'] ?? ''));
$gstin    = strtoupper(trim((string)($input['gstin'] ?? '')));
$message  = trim((string)($input['message'] ?? ''));
$type     = (string)($input['type'] ?? 'bulk');
$consent  = ($input['consent'] ?? '') === '1' || ($input['consent'] ?? '') === 'on';

$digits = preg_replace('/\D/', '', $phoneRaw) ?? '';
if (str_starts_with($digits, '91') && strlen($digits) === 12) {
    $digits = substr($digits, 2);
}

$errors = [];

if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
    $errors['name'] = 'Please enter your name (2–120 characters).';
}
if (strlen($digits) !== 10 || !str_starts_with($digits, '6') && !str_starts_with($digits, '7')
    && !str_starts_with($digits, '8') && !str_starts_with($digits, '9')) {
    $errors['phone'] = 'Enter a valid 10-digit Indian mobile number.';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'That email address does not look right.';
}
if (!array_key_exists($type, $types)) {
    $errors['type'] = 'Choose a valid request type.';
}
if ($city === '' || mb_strlen($city) > 120) {
    if ($type !== 'enquiry') {
        $errors['city'] = 'Please tell us the city or town for dispatch.';
    }
}

$gradeLabel = '';
if ($grade !== '') {
    $found = false;
    foreach ($gradeRows as $row) {
        if ($row['slug'] === $grade) {
            $gradeLabel = $row['name'] . ' — ' . $row['size'] . ' (' . $row['suta'] . ')';
            $found = true;
            break;
        }
    }
    if (!$found) {
        $errors['grade'] = 'Please choose one of the listed grades.';
    }
} elseif ($type === 'bulk') {
    $errors['grade'] = 'Please choose a grade, or leave a note if you are unsure.';
}

if ($type === 'bulk') {
    if ($qty < 5 || $qty > 200000) {
        $errors['quantity_kg'] = 'Bulk orders start at 5 kg. For anything larger, add a note below.';
    }
} elseif ($type === 'sample') {
    $qty = $qty > 0 ? min($qty, 25) : 1;
} else {
    $qty = max(0, min($qty, 200000));
}

if ($gstin !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gstin)) {
    $errors['gstin'] = 'GSTIN format looks off — 15 characters, e.g. 10GEIPK2689D1ZL.';
}

if (mb_strlen($message) > 1500) {
    $errors['message'] = 'Please keep the note under 1500 characters.';
}
if (!$consent) {
    $errors['consent'] = 'Please accept the contact consent so we can respond.';
}

if ($errors) {
    remember_input($input);
    if ($wantsJson) {
        json_response(['ok' => false, 'errors' => $errors, 'message' => 'Please check the highlighted fields.'], 422);
    }
    flash('error', 'Please check the highlighted fields and try again.');
    redirect('/bulk-order');
}

/* ----------------------------------------------------------------- persist */
$estimate = 0;
if ($qty > 0) {
    foreach (products() as $row) {
        if ($row['slug'] === $grade) {
            $estimate = (int)round(((float)$row['indicative_rate']) * $qty);
            break;
        }
    }
}

$reference = order_reference();
$now       = gmdate('Y-m-d H:i:s');

try {
    $stmt = db()->prepare(
        'INSERT INTO orders
         (ref, type, name, business_name, phone, email, city, state, grade, quantity_kg,
          packaging, timeline, gstin, message, estimate, status, ip_hash, user_agent,
          created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $reference,
        $type,
        mb_substr($name, 0, 120),
        mb_substr($business, 0, 160),
        $digits,
        mb_substr($email, 0, 160),
        mb_substr($city, 0, 120),
        mb_substr($state, 0, 120),
        $gradeLabel !== '' ? $gradeLabel : $grade,
        $qty,
        mb_substr($pack, 0, 80),
        mb_substr($timeline, 0, 80),
        mb_substr($gstin, 0, 20),
        mb_substr($message, 0, 1500),
        $estimate,
        'new',
        hash('sha256', $ip),
        mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        $now,
        $now,
    ]);
} catch (Throwable $e) {
    error_log('[shagun] order insert failed: ' . $e->getMessage());
    json_response(['ok' => false, 'message' => 'We could not save your request right now. Please WhatsApp us on ' . SITE_PHONE . '.'], 500);
}

record_rate_limit($bucket);

/* -------------------------------------------------------------------- mail */
$order = [
    'ref'           => $reference,
    'type'          => $type,
    'name'          => mb_substr($name, 0, 120),
    'business_name' => mb_substr($business, 0, 160),
    'phone'         => $digits,
    'email'         => mb_substr($email, 0, 160),
    'city'          => mb_substr($city, 0, 120),
    'state'         => mb_substr($state, 0, 120),
    'grade'         => $gradeLabel !== '' ? $gradeLabel : $grade,
    'quantity_kg'   => $qty,
    'packaging'     => $pack,
    'timeline'      => $timeline,
    'gstin'         => $gstin,
    'message'       => $message,
    'estimate'      => $estimate,
];

if (mailer_enabled()) {
    mailer_send(
        (string)(CONFIG['mail']['to'] ?? SITE_EMAIL),
        'New ' . ($types[$type] ?? 'quote') . ' request — ' . $reference . ' — ' . $name,
        order_notification_html($order),
        $email !== '' ? $email : null
    );
    if ($email !== '') {
        mailer_send($email, 'We have your makhana request (' . $reference . ')', order_ack_html($order));
    }
}

clear_old();

if ($wantsJson) {
    json_response([
        'ok'        => true,
        'reference' => $reference,
        'message'   => 'Request received.',
        'quantity'  => $qty,
        'grade'     => $gradeLabel !== '' ? $gradeLabel : $grade,
        'whatsapp'  => SITE_WHATSAPP,
    ], 201);
}

flash('success', 'Request received — reference ' . $reference . '. We will revert within one working day.');
redirect('/bulk-order?received=' . urlencode($reference));