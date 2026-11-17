<?php
/**
 * Admin — single order, status updates and notes.
 */
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

require_admin();

$id    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = null;

if (is_post()) {
    $input = require_csrf();
    $id    = (int)($input['id'] ?? 0);

    $newStatus = trim((string)($input['status'] ?? ''));
    $notes     = trim((string)($input['admin_notes'] ?? ''));
    $statuses  = order_statuses();

    if (!array_key_exists($newStatus, $statuses)) {
        $error = 'Unknown status value.';
    } else {
        try {
            $stmt = db()->prepare('UPDATE orders SET status = ?, admin_notes = ?, updated_at = ? WHERE id = ?');
            $stmt->execute([$newStatus, mb_substr($notes, 0, 4000), gmdate('Y-m-d H:i:s'), $id]);
            flash('success', 'Order updated.');
            header('Location: /admin/order.php?id=' . $id, true, 303);
            exit;
        } catch (Throwable $e) {
            error_log('[shagun] order update failed: ' . $e->getMessage());
            $error = 'Could not save the change. Please try again.';
        }
    }
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    admin_header('Order not found');
    echo '<div class="empty">That order does not exist. <a class="link-arrow" href="/admin/orders.php">Back to orders</a></div>';
    admin_footer();
    exit;
}

$statuses = order_statuses();
$types    = order_types();

$waMessage = 'Hello ' . $order['name'] . ', this is ' . SITE_NAME . ' regarding your request '
    . $order['ref'] . ' (' . ($order['grade'] ?: 'makhana') . '). ';
$waLink    = wa_link($waMessage, (string)$order['phone']);

admin_header($order['ref'], ($types[$order['type']] ?? $order['type']) . ' · received ' . fmt_datetime((string)$order['created_at']));
?>

<?php if ($error): ?>
    <div class="alert alert--error" style="margin-bottom:18px"><?= e($error) ?></div>
<?php endif; ?>

<div class="detail-grid">
    <div>
        <div class="admin-panel" style="margin-bottom:18px">
            <div class="admin-panel-head">
                <h3 style="font-size:1rem">Requirement</h3>
                <span class="badge <?= e(status_class((string)$order['status'])) ?>"><?= e(status_label((string)$order['status'])) ?></span>
            </div>
            <div style="padding:20px">
                <dl class="detail-list">
                    <dt>Name</dt><dd><?= e($order['name']) ?></dd>
                    <dt>Business</dt><dd><?= e($order['business_name'] ?: '—') ?></dd>
                    <dt>Phone</dt><dd><a href="tel:<?= e($order['phone']) ?>"><?= e($order['phone']) ?></a></dd>
                    <dt>Email</dt><dd><?= $order['email'] ? '<a href="mailto:' . e($order['email']) . '">' . e($order['email']) . '</a>' : '—' ?></dd>
                    <dt>City</dt><dd><?= e($order['city'] ?: '—') ?><?= $order['state'] ? ', ' . e($order['state']) : '' ?></dd>
                    <dt>Grade</dt><dd><?= e($order['grade'] ?: '—') ?></dd>
                    <dt>Quantity</dt><dd><?= (float)$order['quantity_kg'] > 0 ? number_format((float)$order['quantity_kg']) . ' kg' : '—' ?></dd>
                    <dt>Packing</dt><dd><?= e($order['packaging'] ?: '—') ?></dd>
                    <dt>Timeline</dt><dd><?= e($order['timeline'] ?: '—') ?></dd>
                    <dt>GSTIN</dt><dd><?= e($order['gstin'] ?: '—') ?></dd>
                    <dt>Indicative value</dt><dd><?= (int)$order['estimate'] > 0 ? inr($order['estimate']) : '—' ?></dd>
                    <dt>Received</dt><dd><?= e(fmt_datetime((string)$order['created_at'])) ?></dd>
                    <dt>Last update</dt><dd><?= e(fmt_datetime((string)$order['updated_at'])) ?></dd>
                </dl>

                <?php if (!empty($order['message'])): ?>
                    <div style="margin-top:18px">
                        <h4>Buyer note</h4>
                        <div class="detail-note" style="margin-top:8px"><?= e($order['message']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-head">
                <h3 style="font-size:1rem">Update</h3>
            </div>
            <form method="post" action="/admin/order.php" style="padding:20px">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                <div class="form-grid">
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field field--full">
                        <label for="admin_notes">Internal notes <span class="opt">(visible to this buyer on the tracking page)</span></label>
                        <textarea id="admin_notes" name="admin_notes" maxlength="4000" placeholder="Rate quoted, sample dispatched, loading date…"><?= e($order['admin_notes']) ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Save changes</button>
                    <a class="btn btn-ghost" href="/admin/orders.php">Back to orders</a>
                </div>
            </form>
        </div>
    </div>

    <aside style="display:flex;flex-direction:column;gap:16px">
        <div class="admin-card">
            <div class="k">Contact the buyer</div>
            <div class="stack" style="margin-top:12px">
                <a class="btn btn-primary btn-sm btn-block" href="<?= e($waLink) ?>" target="_blank" rel="noopener">WhatsApp</a>
                <a class="btn btn-ghost btn-sm btn-block" href="tel:<?= e($order['phone']) ?>">Call <?= e($order['phone']) ?></a>
                <?php if (!empty($order['email'])): ?>
                    <a class="btn btn-ghost btn-sm btn-block" href="mailto:<?= e($order['email']) ?>?subject=<?= e(rawurlencode('Your makhana request ' . $order['ref'])) ?>">Email buyer</a>
                <?php endif; ?>
                <a class="btn btn-ghost btn-sm btn-block" href="/track?ref=<?= e(rawurlencode((string)$order['ref'])) ?>" target="_blank" rel="noopener">Preview tracking page</a>
            </div>
        </div>

        <div class="admin-card">
            <div class="k">Quick status</div>
            <div class="stack" style="margin-top:12px">
                <?php
                $quick = [
                    'contacted' => 'Mark contacted',
                    'quoted'    => 'Mark quote sent',
                    'confirmed' => 'Confirm order',
                    'dispatched'=> 'Mark dispatched',
                ];
                foreach ($quick as $key => $label):
                    if ($order['status'] === $key) {
                        continue;
                    }
                    ?>
                    <form method="post" action="/admin/order.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="<?= e($key) ?>">
                        <input type="hidden" name="admin_notes" value="<?= e($order['admin_notes']) ?>">
                        <button class="btn btn-ghost btn-sm btn-block" type="submit"><?= e($label) ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="price-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            <span>Notes here are shown to the buyer on the public tracking page. Keep internal pricing remarks out of this field.</span>
        </div>
    </aside>
</div>

<?php admin_footer(); ?>