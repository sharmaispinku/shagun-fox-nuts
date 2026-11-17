<?php
/**
 * Admin authentication. Session-based, single account stored in settings.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

const ADMIN_SESSION_TTL = 28800; // 8 hours

function admin_user(): ?string
{
    $sess = $_SESSION['admin'] ?? null;
    if (!is_array($sess) || empty($sess['user'])) {
        return null;
    }
    if (time() - (int)($sess['at'] ?? 0) > ADMIN_SESSION_TTL) {
        unset($_SESSION['admin']);
        return null;
    }
    return (string)$sess['user'];
}

function admin_is_logged_in(): bool
{
    return admin_user() !== null;
}

function admin_credentials(): array
{
    return [
        'user' => (string)setting('admin_user', 'admin'),
        'hash' => (string)setting('admin_pass_hash', ''),
    ];
}

/**
 * Seconds remaining in the brute-force cooldown (0 when not locked out).
 */
function admin_lock_seconds(): int
{
    $fails = (int)($_SESSION['admin_fails'] ?? 0);
    if ($fails < 8) {
        return 0;
    }
    $since = (int)($_SESSION['admin_fails_since'] ?? 0);
    return max(0, 300 - (time() - $since));
}

/**
 * Verify credentials, with a short delay and a cooldown after repeated
 * failures to slow brute force without needing a database table.
 */
function admin_attempt(string $user, string $password): bool
{
    $creds = admin_credentials();

    if ($creds['hash'] === '') {
        return false;
    }

    if (admin_lock_seconds() > 0) {
        return false;
    }

    $userOk = hash_equals(strtolower($creds['user']), strtolower($user));
    $passOk = password_verify($password, $creds['hash']);

    if (!$userOk || !$passOk) {
        $_SESSION['admin_fails'] = (int)($_SESSION['admin_fails'] ?? 0) + 1;
        if (empty($_SESSION['admin_fails_since'])) {
            $_SESSION['admin_fails_since'] = time();
        }
        usleep(350000);
        return false;
    }

    unset($_SESSION['admin_fails'], $_SESSION['admin_fails_since']);
    session_regenerate_id(true);
    $_SESSION['admin'] = ['user' => $user, 'at' => time()];
    return true;
}

function require_admin(): string
{
    $user = admin_user();
    if ($user === null) {
        $target = $_SERVER['REQUEST_URI'] ?? '/admin/';
        header('Location: /admin/?next=' . urlencode($target), true, 302);
        exit;
    }
    return $user;
}

function admin_logout(): void
{
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}

function admin_nav(string $current): string
{
    $items = [
        '/admin/'              => 'Overview',
        '/admin/orders.php'    => 'Orders',
        '/admin/products.php'  => 'Rates & grades',
        '/admin/account.php'   => 'Account',
        '/'                    => 'View site',
    ];
    $html = '<nav>';
    foreach ($items as $href => $label) {
        $active = rtrim($href, '/') === rtrim($current, '/') ? ' class="is-active"' : '';
        $target = $href === '/' ? ' target="_blank" rel="noopener"' : '';
        $html .= '<a href="' . e($href) . '"' . $active . $target . '>' . e($label) . '</a>';
    }
    $html .= '<a href="/admin/logout.php">Sign out</a>';
    return $html . '</nav>';
}