<?php
/**
 * Shared admin chrome.
 */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function admin_header(string $title, string $subtitle = ''): void
{
    $user = admin_user() ?? 'admin';
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> — <?= e(SITE_NAME) ?> admin</title>
<link rel="icon" href="/assets/favicon/favicon.ico" sizes="any">
<link rel="stylesheet" href="/assets/css/fonts.css">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-side">
        <div class="brand">
            <img class="brand-mark" src="/assets/img/logo-mark.png" alt="" width="480" height="231">
            <span class="brand-name">Shagun<small>Admin</small></span>
        </div>
        <?= admin_nav($_SERVER['PHP_SELF'] ?? '/') ?>
        <div class="spacer"></div>
        <p style="font-size:.74rem;color:rgba(255,255,255,.5)">
            Signed in as <?= e($user) ?><br>
            <a href="/" target="_blank" rel="noopener" style="color:rgba(255,255,255,.75)">Open website ↗</a>
        </p>
    </aside>
    <div class="admin-main">
        <div class="admin-top">
            <div class="admin-title">
                <h1><?= e($title) ?></h1>
                <?php if ($subtitle !== ''): ?><p><?= e($subtitle) ?></p><?php endif; ?>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a class="btn btn-primary btn-sm" href="/bulk-order" target="_blank" rel="noopener">Preview order form</a>
            </div>
        </div>
    <?php
}

function admin_footer(): void
{
    ?>
    </div>
</div>
</body>
</html>
    <?php
}