<?php
/**
 * Shared helpers: escaping, formatting, CSRF, sessions/flash, rate limiting,
 * catalog lookups and admin-side order helpers.
 */

declare(strict_types=1);

/* ------------------------------------------------------------------ escaping */

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function attr(mixed $value): string
{
    return e($value);
}

/* ---------------------------------------------------------------- formatting */

/**
 * Indian-format currency, e.g. 123456 -> ₹1,23,456
 */
function inr(float|int|string $amount, int $decimals = 0): string
{
    $num    = (float)$amount;
    $neg    = $num < 0;
    $num    = abs($num);
    $rounded = round($num, $decimals);

    $parts  = explode('.', number_format($rounded, $decimals, '.', ''));
    $whole  = $parts[0];
    $fraction = $parts[1] ?? '';

    $last3  = substr($whole, -3);
    $rest   = substr($whole, 0, -3);
    if ($rest !== '') {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $whole = $rest . ',' . $last3;
    }

    $out = '₹' . $whole;
    if ($decimals > 0 && $fraction !== '') {
        $out .= '.' . $fraction;
    }
    return ($neg ? '−' : '') . $out;
}

function number_inr(float|int|string $amount): string
{
    return inr($amount, 0);
}

function fmt_date(?string $datetime, string $format = 'd M Y'): string
{
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '—';
}

function fmt_datetime(?string $datetime): string
{
    return fmt_date($datetime, 'd M Y, h:i A');
}

function truncate(string $text, int $length = 120): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return mb_strlen($text) <= $length ? $text : mb_substr($text, 0, $length - 1) . '…';
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/* ------------------------------------------------------------- request state */

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    // No X-Forwarded-For trust by default: it is trivially spoofed.
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function redirect(string $to, int $status = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . $to, true, $status);
    }
    exit;
}

/* --------------------------------------------------------------------- flash */

function flash(string $type, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$type] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

/* --------------------------------------------------------------- old input */

function remember_input(array $data, array $except = ['password', 'website']): void
{
    $_SESSION['old'] = array_diff_key($data, array_flip($except));
}

function old(string $key, string $default = ''): string
{
    $value = $_SESSION['old'][$key] ?? $default;
    return is_scalar($value) ? (string)$value : $default;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

/* --------------------------------------------------------------------- CSRF */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token): bool
{
    $known = $_SESSION['csrf'] ?? '';
    return is_string($token) && $known !== '' && hash_equals($known, $token);
}

/**
 * Reads and validates the CSRF token from a POST body. Returns the raw input.
 */
function require_csrf(): array
{
    $input = $_POST;
    if (!csrf_valid($input['csrf'] ?? null)) {
        if (wants_json()) {
            json_response(['ok' => false, 'message' => 'Your session expired. Please reload the page and try again.'], 419);
        }
        flash('error', 'Your session expired. Please reload the page and try again.');
        redirect(local_referer());
    }
    return $input;
}

/**
 * The referring page, but only when it is on this site — never trust an
 * arbitrary URL for a redirect target.
 */
function local_referer(string $fallback = '/'): string
{
    $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
    if ($ref === '') {
        return $fallback;
    }
    $host = parse_url($ref, PHP_URL_HOST);
    $self = parse_url($_SERVER['HTTP_HOST'] ?? '', PHP_URL_HOST) ?: ($_SERVER['SERVER_NAME'] ?? '');
    if ($host === null || strcasecmp((string)$host, (string)$self) !== 0) {
        return $fallback;
    }
    $path = parse_url($ref, PHP_URL_PATH) ?: '/';
    $query = parse_url($ref, PHP_URL_QUERY);
    return $path . ($query ? '?' . $query : '');
}

function wants_json(): bool
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_contains($accept, 'application/json');
}

/* -------------------------------------------------------------- rate limits */

/**
 * Sliding-window throttle backed by the submissions table.
 */
function rate_limited(string $bucket, int $limit, int $windowSeconds): bool
{
    try {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM rate_limits WHERE bucket = ? AND created_at >= ?'
        );
        $stmt->execute([$bucket, gmdate('Y-m-d H:i:s', time() - $windowSeconds)]);
        return (int)$stmt->fetchColumn() >= $limit;
    } catch (Throwable $e) {
        error_log('[shagun] rate limit check failed: ' . $e->getMessage());
        return false; // fail open — never block a genuine buyer over a DB hiccup
    }
}

function record_rate_limit(string $bucket): void
{
    try {
        $stmt = db()->prepare('INSERT INTO rate_limits (bucket, created_at) VALUES (?, ?)');
        $stmt->execute([$bucket, gmdate('Y-m-d H:i:s')]);

        // Opportunistic housekeeping: keep the throttle table small.
        if (random_int(1, 100) === 1) {
            db()->prepare('DELETE FROM rate_limits WHERE created_at < ?')
                ->execute([gmdate('Y-m-d H:i:s', time() - 86400)]);
        }
    } catch (Throwable $e) {
        error_log('[shagun] rate limit write failed: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------------ settings */

function setting(string $key, mixed $default = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT k, v FROM settings') as $row) {
                $cache[(string)$row['k']] = $row['v'];
            }
        } catch (Throwable $e) {
            error_log('[shagun] settings unavailable: ' . $e->getMessage());
        }
    }
    $value = $cache[$key] ?? null;
    return $value === null ? $default : $value;
}

/* ------------------------------------------------------------------- catalog */

/**
 * @return array<int, array<string, mixed>>
 */
function products(bool $activeOnly = true): array
{
    static $cache = [];
    $key = $activeOnly ? 'active' : 'all';
    if (!isset($cache[$key])) {
        try {
            $sql = 'SELECT * FROM products' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY sort_order, id';
            $cache[$key] = db()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            error_log('[shagun] products unavailable: ' . $e->getMessage());
            $cache[$key] = [];
        }
    }
    return $cache[$key];
}

function product(string $slug): ?array
{
    foreach (products(false) as $row) {
        if ($row['slug'] === $slug) {
            return $row;
        }
    }
    return null;
}

/**
 * Grade options for selects.
 * @return array<int, array{slug: string, name: string, size: string, suta: string}>
 */
function grade_options(): array
{
    $out = [];
    foreach (products() as $row) {
        $out[] = [
            'slug' => (string)$row['slug'],
            'name' => (string)$row['name'],
            'size' => (string)$row['grade_size'],
            'suta' => (string)$row['suta'],
        ];
    }
    return $out;
}

function product_image(string $slug): ?string
{
    $row = product($slug);
    if (!$row) {
        return null;
    }
    $file = trim((string)($row['image'] ?? ''));
    if ($file === '') {
        return null;
    }
    return 'assets/img/' . ltrim($file, '/');
}

function product_features(array $row): array
{
    $raw = $row['features'] ?? '';
    if (is_array($raw)) {
        return array_values(array_filter($raw));
    }
    $decoded = json_decode((string)$raw, true);
    return is_array($decoded) ? array_values(array_filter($decoded)) : [];
}

/* -------------------------------------------------------------------- orders */

/**
 * Order lifecycle. Keys are the values stored in the database.
 */
function order_statuses(): array
{
    return [
        'new'        => 'New request',
        'contacted'  => 'Contacted',
        'quoted'     => 'Quote sent',
        'confirmed'  => 'Order confirmed',
        'dispatched' => 'Dispatched',
        'completed'  => 'Completed',
        'closed'     => 'Closed',
        'rejected'   => 'Rejected',
    ];
}

function order_types(): array
{
    return [
        'bulk'         => 'Bulk purchase',
        'sample'       => 'Sample request',
        'distributor'  => 'Distributor enquiry',
        'enquiry'      => 'General enquiry',
    ];
}

function status_label(string $status): string
{
    return order_statuses()[$status] ?? ucfirst($status);
}

function status_class(string $status): string
{
    return 'is-' . preg_replace('/[^a-z]+/', '-', strtolower($status));
}

function order_reference(): string
{
    return 'SHG-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

/* ---------------------------------------------------------------- navigation */

function nav_items(): array
{
    return [
        ['label' => 'Home',        'href' => '/'],
        ['label' => 'Grades',      'href' => '/products'],
        ['label' => 'Market',      'href' => '/market'],
        ['label' => 'Bulk Order',  'href' => '/bulk-order'],
        ['label' => 'About',       'href' => '/about'],
        ['label' => 'Contact',     'href' => '/contact'],
    ];
}

function nav_active(string $href): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . trim($path, '/');
    if ($href === '/') {
        return $path === '/' ? ' class="is-active"' : '';
    }
    return $path === $href || str_starts_with($path, $href . '/') ? ' class="is-active"' : '';
}

/* --------------------------------------------------------------- whatsapp */

function wa_link(string $message, ?string $phone = null): string
{
    $phone = preg_replace('/\D/', '', $phone ?? SITE_WHATSAPP) ?: SITE_WHATSAPP;
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
}

function tel_link(): string
{
    return 'tel:' . preg_replace('/[^\d+]/', '', SITE_PHONE);
}

/* ---------------------------------------------------------------- misc */

function json_attr(array $data): string
{
    return e(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
}

function asset(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $file = APP_ROOT . $path;
    $ver  = is_file($file) ? (string)filemtime($file) : '1';
    return $path . '?v=' . $ver;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}