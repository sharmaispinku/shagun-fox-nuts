<?php
/**
 * Admin — order queue with filters, pagination and CSV export.
 */
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

require_admin();

$q      = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$type   = trim((string)($_GET['type'] ?? ''));
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 25;

$statuses = order_statuses();
$types    = order_types();

if ($status !== '' && !array_key_exists($status, $statuses)) {
    $status = '';
}
if ($type !== '' && !array_key_exists($type, $types)) {
    $type = '';
}

$where  = [];
$params = [];

if ($q !== '') {
    $where[]  = '(ref LIKE ? OR name LIKE ? OR business_name LIKE ? OR phone LIKE ? OR city LIKE ?)';
    $like     = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($type !== '') {
    $where[] = 'type = ?';
    $params[] = $type;
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countStmt = db()->prepare('SELECT COUNT(*) FROM orders' . $whereSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);

$stmt = db()->prepare(
    'SELECT * FROM orders' . $whereSql . ' ORDER BY created_at DESC, id DESC LIMIT ' . (int)$per
    . ' OFFSET ' . (int)(($page - 1) * $per)
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$baseQuery = static function (array $overrides = []) use ($q, $status, $type, $page): string {
    $p = array_filter([
        'q' => $q, 'status' => $status, 'type' => $type, 'page' => $page,
    ], static fn($v): bool => $v !== '' && $v !== null);
    return '/admin/orders.php?' . http_build_query(array_merge($p, $overrides));
};

$sumStmt = db()->prepare("SELECT
      SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
      SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) AS quoted_count,
      SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
      SUM(CASE WHEN status IN ('dispatched','completed') THEN 1 ELSE 0 END) AS done_count
    FROM orders");
$sumStmt->execute();
$counts = $sumStmt->fetch() ?: [];

admin_header('Orders', $total . ' request' . ($total === 1 ? '' : 's') . ' in the system');
?>

<div class="admin-cards">
    <div class="admin-card"><div class="k">New</div><div class="v"><?= (int)($counts['new_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Quoted</div><div class="v"><?= (int)($counts['quoted_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Confirmed</div><div class="v"><?= (int)($counts['confirmed_count'] ?? 0) ?></div></div>
    <div class="admin-card"><div class="k">Dispatched / done</div><div class="v"><?= (int)($counts['done_count'] ?? 0) ?></div></div>
</div>

<div class="admin-panel">
    <div class="admin-panel-head">
        <form class="filters" method="get" action="/admin/orders.php">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Reference, name, phone, city"
                style="padding:8px 14px;border-radius:999px;border:1px solid var(--line-strong);font-size:.86rem;min-width:240px">
            <select name="status" style="padding:8px 14px;border-radius:999px;border:1px solid var(--line-strong);font-size:.86rem">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" style="padding:8px 14px;border-radius:999px;border:1px solid var(--line-strong);font-size:.86rem">
                <option value="">All types</option>
                <?php foreach ($types as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
            <a class="btn btn-ghost btn-sm" href="/admin/orders.php">Reset</a>
        </form>
        <a class="btn btn-ghost btn-sm" href="<?= e($baseQuery(['export' => 'csv', 'page' => 1])) ?>">Export CSV</a>
    </div>

    <?php if (!$rows): ?>
        <div class="empty">No requests match these filters.</div>
    <?php else: ?>
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Buyer</th>
                        <th>Contact</th>
                        <th>Requirement</th>
                        <th>Indicative</th>
                        <th>Status</th>
                        <th>Received</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><a class="ref" href="/admin/order.php?id=<?= (int)$r['id'] ?>"><?= e($r['ref']) ?></a></td>
                            <td><?= e($types[$r['type']] ?? $r['type']) ?></td>
                            <td>
                                <strong><?= e($r['name']) ?></strong>
                                <?php if (!empty($r['business_name'])): ?>
                                    <div class="sub"><?= e($r['business_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a>
                                <div class="sub"><?= e($r['city'] ?: '—') ?><?= $r['state'] ? ', ' . e($r['state']) : '' ?></div>
                                <?php if (!empty($r['email'])): ?>
                                    <div class="sub"><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e($r['grade'] ?: '—') ?>
                                <?php if ((float)$r['quantity_kg'] > 0): ?>
                                    <div class="sub"><?= number_format((float)$r['quantity_kg']) ?> kg</div>
                                <?php endif; ?>
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

<?php if ($pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e($baseQuery(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php admin_footer(); ?>