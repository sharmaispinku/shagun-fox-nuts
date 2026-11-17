<?php
/**
 * Transactional mail helpers.
 *
 * Uses PHP mail() by default (works on nearly every shared host). Set
 * mail.mode = 'log' in config.php to write to data/mail.log instead — handy
 * while testing before the domain's SPF/DKIM records are live.
 */

declare(strict_types=1);

function mailer_enabled(): bool
{
    return (bool)(CONFIG['mail']['enabled'] ?? false);
}

/**
 * @return bool true when the message was handed to the transport
 */
function mailer_send(string $to, string $subject, string $html, ?string $replyTo = null): bool
{
    $cfg    = CONFIG['mail'];
    $from   = (string)($cfg['from'] ?? ('no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost')));
    $fromNm = (string)($cfg['from_name'] ?? SITE_NAME);

    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . mb_encode_mimeheader($fromNm, 'UTF-8') . ' <' . $from . '>',
    ];
    if ($replyTo) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    if (($cfg['mode'] ?? 'mail') === 'log') {
        $line = str_repeat('=', 72) . "\n"
            . 'TO: ' . $to . "\n"
            . 'SUBJECT: ' . $subject . "\n"
            . 'FROM: ' . $from . "\n"
            . 'REPLY-TO: ' . ($replyTo ?? '-') . "\n"
            . 'TIME: ' . date('c') . "\n"
            . str_repeat('-', 72) . "\n"
            . trim(strip_tags(str_replace(['<br>', '</p>', '</div>', '</tr>'], "\n", $html))) . "\n\n";

        $dir = APP_ROOT . '/data';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX) !== false;
    }

    return @mail($to, $subject, $html, implode("\r\n", $headers));
}

function mailer_wrapper(string $title, string $bodyHtml): string
{
    $year = date('Y');
    return '<!doctype html><html><body style="margin:0;padding:24px;background:#f4f2ec;'
        . 'font-family:Inter,Arial,sans-serif;color:#1a1f1a">'
        . '<div style="max-width:620px;margin:0 auto;background:#fff;border-radius:16px;padding:32px">'
        . '<h1 style="font-size:20px;margin:0 0 4px">' . e($title) . '</h1>'
        . '<p style="margin:0 0 20px;color:#6b7a6b;font-size:13px">' . e(SITE_NAME) . ' · website enquiry</p>'
        . $bodyHtml
        . '<hr style="border:0;border-top:1px solid #e6e2d8;margin:28px 0 16px">'
        . '<p style="font-size:12px;color:#6b7a6b;margin:0">' . e(SITE_NAME) . ' · '
        . e(CONFIG['site']['phone'] ?? '') . ' · ' . e(SITE_EMAIL) . '<br>'
        . 'Sent automatically from the website quote form. &copy; ' . $year . '</p>'
        . '</div></body></html>';
}

/**
 * HTML body for the internal "new request" notification.
 */
function order_notification_html(array $order): string
{
    $types = order_types();
    $rows  = [
        'Reference'    => $order['ref'],
        'Type'         => $types[$order['type']] ?? $order['type'],
        'Name'         => $order['name'],
        'Business'     => $order['business_name'] ?: '—',
        'Phone'        => $order['phone'],
        'Email'        => $order['email'] ?: '—',
        'Location'     => trim(($order['city'] ?: '') . ', ' . ($order['state'] ?: '')) ?: '—',
        'Grade'        => $order['grade'] ?: '—',
        'Quantity'     => $order['quantity_kg'] > 0 ? number_format((float)$order['quantity_kg'], 0) . ' kg' : '—',
        'Packaging'    => $order['packaging'] ?: '—',
        'Timeline'     => $order['timeline'] ?: '—',
        'GSTIN'        => $order['gstin'] ?: '—',
        'Estimate'     => $order['estimate'] > 0 ? inr($order['estimate']) : '—',
    ];

    $table = '<table style="width:100%;border-collapse:collapse;font-size:14px">';
    foreach ($rows as $label => $value) {
        $table .= '<tr><td style="padding:7px 0;width:34%;color:#6b7a6b;vertical-align:top">' . e($label) . '</td>'
            . '<td style="padding:7px 0;font-weight:600">' . e($value) . '</td></tr>';
    }
    $table .= '</table>';

    $note = trim((string)($order['message'] ?? ''));
    $noteHtml = $note !== ''
        ? '<h3 style="font-size:14px;margin:22px 0 6px">Buyer note</h3>'
            . '<p style="margin:0;padding:14px;background:#f7f5ef;border-radius:10px;white-space:pre-line">' . e($note) . '</p>'
        : '';

    return mailer_wrapper('New ' . ($types[$order['type']] ?? 'quote request'), $table . $noteHtml
        . '<p style="margin:22px 0 0;font-size:13px">Reply to this email or call the buyer on '
        . e($order['phone']) . '.</p>');
}

/**
 * Auto-acknowledgement sent to the buyer.
 */
function order_ack_html(array $order): string
{
    $grade = $order['grade'] ?: 'makhana';
    $qty   = $order['quantity_kg'] > 0 ? number_format((float)$order['quantity_kg'], 0) . ' kg' : 'your requirement';

    return mailer_wrapper('We have your request', '<p style="margin:0 0 16px;font-size:15px">Dear '
        . e($order['name']) . ',</p>'
        . '<p style="margin:0 0 16px;font-size:15px">Thank you for writing to ' . e(SITE_NAME)
        . '. Your request for <strong>' . e($qty) . ' of ' . e($grade) . '</strong> has been logged '
        . 'with reference <strong>' . e($order['ref']) . '</strong>.</p>'
        . '<p style="margin:0 0 16px;font-size:15px">Our grading desk will check availability and revert '
        . 'with a firm quote, packing options and dispatch schedule within one working day.</p>'
        . '<p style="margin:0 0 8px;font-size:14px;color:#6b7a6b">Need it faster? WhatsApp us on '
        . '<strong>' . e(SITE_PHONE) . '</strong> quoting ' . e($order['ref']) . '.</p>');
}