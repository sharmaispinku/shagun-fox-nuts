<?php
/**
 * Admin — sign in, or the overview dashboard when already signed in.
 */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

$error = null;

if (is_post()) {
    $input = require_csrf();
    $user  = trim((string)($input['username'] ?? ''));
    $pass  = (string)($input['password'] ?? '');

    if (admin_lock_seconds() > 0) {
        $error = 'Too many failed attempts. Try again in ' . admin_lock_seconds() . ' seconds.';
    } elseif (admin_attempt($user, $pass)) {
        $next = (string)($_POST['next'] ?? '');
        if ($next === '' || !str_starts_with($next, '/admin')) {
            $next = '/admin/orders.php';
        }
        header('Location: ' . $next, true, 302);
        exit;
    } else {
        $error = 'Those credentials did not match.';
    }
}

/* ---------------------------------------------------------------- sign in */
if (!admin_is_logged_in()) {
    $next = (string)($_GET['next'] ?? '');
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Sign in — <?= e(SITE_NAME) ?> admin</title>
<link rel="icon" href="/assets/favicon/favicon.ico" sizes="any">
<link rel="stylesheet" href="/assets/css/fonts.css">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="admin-body">
<div class="login-wrap">
    <form class="login-card" method="post" action="/admin/">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <div class="brand">
            <img class="brand-mark" src="/assets/img/logo-mark.png" alt="" width="480" height="231">
            <span class="brand-name" style="color:var(--ink)">Shagun<small>Admin</small></span>
        </div>

        <h1>Sign in</h1>
        <p>Order desk for <?= e(SITE_NAME) ?>.</p>

        <?php if ($error): ?>
            <div class="alert alert--error" style="margin-bottom:16px"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username" autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <div class="form-actions">
            <button class="btn btn-primary btn-block" type="submit">Sign in</button>
        </div>
        <p class="small muted" style="margin-top:18px">Forgotten the password? Use the Account page after signing in, or update the <code>settings</code> table directly.</p>
    </form>
</div>
</body>
</html>
    <?php
    exit;
}

/* -------------------------------------------------------------- overview */
require_once __DIR__ . '/_layout.php';

$statuses = order_statuses();
$types    = order_types();

$stats = db()->prepare("SELECT
      SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
      SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) AS contacted_count,
      SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) AS quoted_count,
      SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
      SUM(CASE WHEN status IN ('dispatched','completed') THEN 1 ELSE 0 END) AS done_count,
      SUM(CASE WHEN status IN ('confirmed','dispatched','completed') THEN estimate ELSE 0 END) AS pipeline_value,
      COUNT(*) AS total_count
    FROM orders");
$stats->execute();
$s = $stats->fetch() ?: [];

$recent = db()->query('SELECT * FROM orders ORDER BY created_at DESC, id DESC LIMIT 6')->fetchAll();

$trend = db()->query("SELECT substr(created_at, 1, 7) AS month, COUNT(*) AS c
    FROM orders GROUP BY month ORDER BY month DESC LIMIT 6")->fetchAll();

admin_header('Overview', 'What the order desk needs to act on today');
?>

<div class="admin-cards">
    <div class="admin-card"><div class="k">New requests</div><div class="v"><?= (int)($s['new_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Awaiting quote</div><div class="v"><?= (int)($s['quoted_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Confirmed</div><div class="v"><?= (int)($s['confirmed_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Dispatched / done</div><div class="v"><?= (int)($s['done_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Pipeline value</div><div class="v"><?= inr((float)($s['pipeline_value'] ?? 0)) ?></div></div>
</div>

<div class="detail-grid">
    <div class="admin-panel">
        <div class="admin-panel-head">
            <h3 style="font-size:1rem">Latest requests</h3>
            <a class="btn btn-ghost btn-sm" href="/admin/orders.php">View all</a>
        </div>
        <?php if (!$recent): ?>
            <div class="empty">No requests yet. Share the bulk order link to start receiving them.</div>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table class="admin-table">
                    <thead>
                        <tr><th>Reference</th><th>Buyer</th><th>Requirement</th><th>Value</th><th>Status</th><th>Received</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><a class="ref" href="/admin/order.php?id=<?= (int)$r['id'] ?>"><?= e($r['ref']) ?></a></td>
                                <td>
                                    <strong><?= e($r['name']) ?></strong>
                                    <div class="sub"><?= e($r['city'] ?: '—') ?> · <?= e($r['phone']) ?></div>
                                </td>
                                <td>
                                    <?= e($r['grade'] ?: '—') ?>
                                    <div class="sub"><?= (float)$r['quantity_kg'] > 0 ? number_format((float)$r['quantity_kg']) . ' kg' : e($types[$r['type']] ?? $r['type']) ?></div>
                                </td>
                                <td><?= (int)$r['estimate'] > 0 ? inr($r['estimate']) : '—' ?></td>
                                <td><span class="badge <?= e(status_class((string)$r['status'])) ?>"><?= e(status_label((string)$r['status'])) ?></span></td>
                                <td class="sub"><?= e(fmt_date((string)$r['created_at'], 'd M, H:i')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <aside style="display:flex;flex-direction:column;gap:16px">
        <div class="admin-card">
            <div class="k">Requests by month</div>
            <?php if (!$trend): ?>
                <p class="small muted" style="margin-top:10px">No data yet.</p>
            <?php else: ?>
                <div class="stack" style="margin-top:10px">
                    <?php foreach ($trend as $t): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:.86rem">
                            <span><?= e(date('M Y', (int)strtotime($t['month'] . '-01'))) ?></span>
                            <span class="tag"><?= (int)$t['c'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <div class="k">Desk actions</div>
            <div class="stack" style="margin-top:12px">
                <a class="btn btn-ghost btn-sm btn-block" href="/admin/orders.php?status=new">Work the new queue</a>
                <a class="btn btn-ghost btn-sm btn-block" href="/admin/products.php">Update indicative rates</a>
                <a class="btn btn-ghost btn-sm btn-block" href="/admin/account.php">Change admin password</a>
                <a class="btn btn-primary btn-sm btn-block" href="/admin/export.php">Export all orders</a>
            </div>
        </div>

        <div class="price-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            <span><?= (int)($s['total_count'] ?? 0) ?> request(s) stored. Back up <code>data/</code> regularly — orders live in the database file.</span>
        </div>
    </aside>
</div>

<?php admin_footer(); ?>