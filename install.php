<?php
/**
 * Installer — creates the schema, seeds the catalogue and sets the admin login.
 *
 * Delete this file after a successful install.
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$lockFile = APP_ROOT . '/data/installed.lock';
$already  = is_file($lockFile);
$error    = null;
$done     = false;
$dbInfo   = db_driver() === 'mysql'
    ? 'MySQL · ' . (CONFIG['db']['host'] ?? 'localhost') . '/' . (CONFIG['db']['name'] ?? '')
    : 'SQLite · ' . (CONFIG['db']['path'] ?? '');

$preflight = [
    'config.php present'      => is_file(APP_ROOT . '/config.php'),
    'data/ writable'          => is_writable(APP_ROOT . '/data') || is_writable(APP_ROOT),
    'PDO available'           => class_exists(PDO::class),
    'driver ' . db_driver()   => db_driver() === 'mysql' ? extension_loaded('pdo_mysql') : extension_loaded('pdo_sqlite'),
];

if (!$already) {
    try {
        db()->query('SELECT 1');
        $preflight['database reachable'] = true;
    } catch (Throwable $e) {
        $preflight['database reachable'] = false;
        $error = 'Database connection failed: ' . $e->getMessage();
    }
}

if (is_post() && !$already) {
    $input = require_csrf();

    $adminUser = trim((string)($input['admin_user'] ?? 'admin'));
    $adminPass = (string)($input['admin_pass'] ?? '');
    $adminMail = trim((string)($input['admin_email'] ?? SITE_EMAIL));

    if (!preg_match('/^[A-Za-z0-9_.-]{3,32}$/', $adminUser)) {
        $error = 'Admin username must be 3–32 characters: letters, numbers, dot, underscore or dash.';
    } elseif (strlen($adminPass) < 10) {
        $error = 'Admin password must be at least 10 characters.';
    } elseif (!filter_var($adminMail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid admin email address.';
    } elseif (in_array(false, $preflight, true)) {
        $error = 'Preflight checks failed. Fix the items marked "Fix needed" and retry.';
    }

    if (!$error) {
        try {
            $schema = APP_ROOT . '/data/schema.' . (db_driver() === 'mysql' ? 'mysql' : 'sqlite') . '.sql';
            db_apply_schema($schema);

            $seed = require APP_ROOT . '/data/seed.php';
            seed_catalogue($seed);

            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $settings = [
                'admin_user'      => $adminUser,
                'admin_pass_hash' => $hash,
                'admin_email'     => $adminMail,
                'rates_updated'   => date('d M Y'),
                'installed_at'    => date('c'),
            ];
            $upsertSql = db_driver() === 'mysql'
                ? 'INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)'
                : 'INSERT INTO settings (k, v) VALUES (?, ?) ON CONFLICT (k) DO UPDATE SET v = excluded.v';
            $stmt = db()->prepare($upsertSql);
            foreach ($settings as $k => $v) {
                $stmt->execute([$k, (string)$v]);
            }

            if (@file_put_contents($lockFile, 'Installed ' . date('c') . ' by ' . $adminUser . "\n", LOCK_EX) === false) {
                error_log('[shagun] could not write install lock — data/ is not writable');
            }
            $done    = true;
            $already = true;
        } catch (Throwable $e) {
            error_log('[shagun] install failed: ' . $e->getMessage());
            $error = 'Install failed: ' . $e->getMessage();
        }
    }
}

/**
 * Insert catalogue rows idempotently.
 */
function seed_catalogue(array $seed): void
{
    $pdo = db();
    $driver = db_driver();

    $select = $pdo->prepare('SELECT COUNT(*) FROM products');
    $select->execute();
    $empty = (int)$select->fetchColumn() === 0;

    $insert = $pdo->prepare(
        'INSERT INTO products
         (slug, name, tagline, grade_size, suta, description, features, moq_kg, indicative_rate,
          pack_sizes, image, badge, sort_order, active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
    );

    foreach ($seed['products'] as $i => $p) {
        if (!$empty) {
            $check = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
            $check->execute([$p['slug']]);
            if ($check->fetchColumn()) {
                continue;
            }
        }
        $insert->execute([
            $p['slug'], $p['name'], $p['tagline'], $p['grade_size'], $p['suta'],
            $p['description'], json_encode($p['features'], JSON_UNESCAPED_UNICODE),
            $p['moq_kg'], $p['indicative_rate'], $p['pack_sizes'], $p['image'], $p['badge'], $i,
        ]);
    }

    $selDist = $pdo->prepare('SELECT COUNT(*) FROM distributors');
    $selDist->execute();
    if ((int)$selDist->fetchColumn() === 0) {
        $insDist = $pdo->prepare('INSERT INTO distributors (name, area, since, note, sort_order) VALUES (?, ?, ?, ?, ?)');
        foreach ($seed['distributors'] as $i => $d) {
            $insDist->execute([$d['name'], $d['area'], $d['since'], $d['note'], $i]);
        }
    }

    $defaults = [
        'rates_updated' => date('d M Y'),
        'admin_user'    => 'admin',
    ];
    $upsert = $pdo->prepare(
        $driver === 'mysql'
            ? 'INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)'
            : 'INSERT INTO settings (k, v) VALUES (?, ?) ON CONFLICT (k) DO UPDATE SET v = excluded.v'
    );
    foreach ($defaults as $k => $v) {
        $upsert->execute([$k, (string)$v]);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Install — <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="/assets/css/fonts.css">
<link rel="stylesheet" href="/assets/css/main.css">
<style>
    body { background: var(--paper); }
    .wrap { max-width: 720px; margin: 0 auto; padding: 48px clamp(18px, 4vw, 40px); }
    .brand { margin-bottom: 26px; }
    .panel { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: clamp(22px, 3vw, 34px); box-shadow: var(--shadow-sm); }
    .check { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 6px 14px; font-size: .88rem; padding: 8px 0; border-bottom: 1px solid var(--line); }
    .ok { color: var(--success); font-weight: 600; }
    .bad { color: var(--danger); font-weight: 600; }
    .field { margin-bottom: 16px; }
    .hint { font-size: .78rem; color: var(--muted); margin-top: 5px; }
</style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <img class="brand-mark" src="/assets/img/logo-mark.png" alt="" width="480" height="231">
    </div>

    <h1 style="font-size:1.9rem"><?= $done ? 'Installation complete' : ($already ? 'Already installed' : 'Install Shagun Enterprises') ?></h1>
    <p class="muted" style="margin:10px 0 24px;font-size:.92rem">
        <?= $done
            ? 'The catalogue is seeded and your admin account is ready.'
            : ($already
                ? 'This site already has a database and an admin account. Delete data/installed.lock to run the installer again.'
                : 'Create the database tables, seed the makhana grades and set your admin login.') ?>
    </p>

    <?php if ($error): ?>
        <div class="alert alert--error" style="margin-bottom:18px"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($done): ?>
        <div class="alert alert--success" style="margin-bottom:20px">
            <div>
                <strong>Done.</strong> Two things before you go live:
                <ol style="margin:8px 0 0 18px">
                    <li>Delete <code>install.php</code> from the server.</li>
                    <li>Make sure <code>data/</code> is writable by PHP (chmod 755, files 644) so orders can be stored.</li>
                </ol>
            </div>
        </div>
        <div class="hero-actions">
            <a class="btn btn-primary" href="/">View site</a>
            <a class="btn btn-ghost" href="/admin/">Admin panel</a>
            <a class="btn btn-ghost" href="/bulk-order">Test the order form</a>
        </div>
    <?php elseif ($already): ?>
        <div class="hero-actions">
            <a class="btn btn-primary" href="/">View site</a>
            <a class="btn btn-ghost" href="/admin/">Admin panel</a>
        </div>
    <?php else: ?>
        <div class="panel" style="margin-bottom:20px">
            <h3 style="font-size:.8rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);margin-bottom:10px">Environment</h3>
            <div class="check"><span>Storage</span><span class="ok"><?= e($dbInfo) ?></span></div>
            <?php foreach ($preflight as $label => $ok): ?>
                <div class="check">
                    <span><?= e($label) ?></span>
                    <span class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? 'OK' : 'Fix needed' ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="panel" method="post" action="/install.php">
            <?= csrf_field() ?>
            <div class="field">
                <label for="admin_user">Admin username</label>
                <input type="text" id="admin_user" name="admin_user" value="admin" required
                    pattern="[A-Za-z0-9_.\-]{3,32}" maxlength="32" autocomplete="username">
                <div class="hint">Used at /admin/ to sign in. Avoid the obvious.</div>
            </div>
            <div class="field">
                <label for="admin_pass">Admin password</label>
                <input type="password" id="admin_pass" name="admin_pass" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters">
                <div class="hint">Stored as a one-way hash. There is no password reset email — keep this safe.</div>
            </div>
            <div class="field">
                <label for="admin_email">Admin email</label>
                <input type="email" id="admin_email" name="admin_email" value="<?= e(SITE_EMAIL) ?>" required autocomplete="email">
                <div class="hint">Used only for your own records and order notifications.</div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary btn-lg" type="submit">Install now</button>
                <span class="form-note">Safe to re-run only if no tables exist yet.</span>
            </div>
        </form>
    <?php endif; ?>

    <p class="muted" style="margin-top:22px;font-size:.8rem">
        <?= e(SITE_NAME) ?> · GSTIN <?= e(CONFIG['site']['gstin'] ?? '—') ?>
    </p>
</div>
</body>
</html>