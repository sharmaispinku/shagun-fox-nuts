<?php
/**
 * Bootstraps every entry point: config, sessions, database, helpers,
 * security headers and error handling.
 */

declare(strict_types=1);

if (defined('SHAGUN_BOOTSTRAPPED')) {
    return;
}
define('SHAGUN_BOOTSTRAPPED', true);
define('SHAGUN_ROOT', __DIR__ . '/..');

$configFile = SHAGUN_ROOT . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration missing. Copy config.sample.php to config.php and fill it in.');
}

$config = require $configFile;

// ---------------------------------------------------------------- error setup
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

set_exception_handler(static function (Throwable $e): void {
    error_log('[shagun] Uncaught ' . get_class($e) . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }
    exit('Something went wrong on our side. Please try again, or WhatsApp us on '
        . ($config['site']['phone'] ?? '') . '.');
});

// --------------------------------------------------------------------- session
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($config['security']['force_https'])
        || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name($config['security']['session_name'] ?? 'shagun_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ------------------------------------------------------------------- constants
define('APP_ROOT', SHAGUN_ROOT);
define('CONFIG', $config);
define('SITE_URL', rtrim((string)($config['site']['url'] ?? ''), '/'));
define('SITE_NAME', (string)($config['site']['name'] ?? 'Shagun Enterprises'));
define('SITE_EMAIL', (string)($config['site']['email'] ?? ''));
define('SITE_PHONE', (string)($config['site']['phone'] ?? ''));
define('SITE_WHATSAPP', (string)($config['site']['whatsapp'] ?? ''));

// ---------------------------------------------------------------------- helpers
require_once APP_ROOT . '/includes/polyfill.php';
require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/mailer.php';

// ------------------------------------------------------------- security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    if (!empty($config['security']['force_https'])) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            header('Location: https://' . $host . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
            exit;
        }
    }
}

/**
 * Send a JSON response and stop.
 */
function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}