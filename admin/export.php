<?php
/**
 * Admin — CSV export of orders (respects the same filters as the list).
 */
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

require_admin();

$q      = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$type   = trim((string)($_GET['type'] ?? ''));

$where  = [];
$params = [];

if ($q !== '') {
    $where[] = '(ref LIKE ? OR name LIKE ? OR business_name LIKE ? OR phone LIKE ? OR city LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($status !== '' && array_key_exists($status, order_statuses())) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($type !== '' && array_key_exists($type, order_types())) {
    $where[] = 'type = ?';
    $params[] = $type;
}

$stmt = db()->prepare('SELECT * FROM orders' . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY created_at DESC, id DESC');
$stmt->execute($params);
$rows = $stmt->fetchAll();

$filename = 'shagun-orders-' . date('Y-m-d-His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads UTF-8 correctly

$types = order_types();
$statuses = order_statuses();

fputcsv($out, [
    'Reference', 'Received', 'Type', 'Name', 'Business', 'Phone', 'Email', 'City', 'State',
    'Grade', 'Quantity (kg)', 'Packing', 'Timeline', 'GSTIN', 'Indicative value (INR)',
    'Status', 'Updated', 'Buyer note', 'Internal notes',
]);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['ref'],
        $r['created_at'],
        $types[$r['type']] ?? $r['type'],
        $r['name'],
        $r['business_name'],
        $r['phone'],
        $r['email'],
        $r['city'],
        $r['state'],
        $r['grade'],
        $r['quantity_kg'],
        $r['packaging'],
        $r['timeline'],
        $r['gstin'],
        $r['estimate'],
        $statuses[$r['status']] ?? $r['status'],
        $r['updated_at'],
        $r['message'],
        $r['admin_notes'],
    ]);
}

fclose($out);
exit;