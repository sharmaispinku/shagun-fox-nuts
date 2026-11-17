<?php
/**
 * Admin — grades, indicative rates and MOQ.
 */
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

require_admin();

$error = null;

if (is_post()) {
    $input = require_csrf();

    if (($input['action'] ?? '') === 'rates') {
        try {
            $pdo    = db();
            $update = $pdo->prepare('UPDATE products SET indicative_rate = ?, moq_kg = ?, pack_sizes = ?, badge = ?, active = ? WHERE id = ?');

            foreach ($input['rate'] ?? [] as $id => $rate) {
                $update->execute([
                    max(0, (int)$rate),
                    max(1, (int)($input['moq'][$id] ?? 25)),
                    mb_substr(trim((string)($input['pack'][$id] ?? '')), 0, 255),
                    mb_substr(trim((string)($input['badge'][$id] ?? '')), 0, 60),
                    isset($input['active'][$id]) ? 1 : 0,
                    (int)$id,
                ]);
            }

            $pdo->prepare('UPDATE settings SET v = ? WHERE k = ?')->execute([date('d M Y'), 'rates_updated']);
            flash('success', 'Rates updated — the public rate table now shows ' . date('d M Y') . '.');
        } catch (Throwable $e) {
            error_log('[shagun] rate update failed: ' . $e->getMessage());
            $error = 'Could not save rates. Check the database connection.';
        }
    }
}

$gradeRows = products(false);
$ratesDate = (string)setting('rates_updated', '—');

admin_header('Rates & grades', 'Indicative wholesale rate per kg, MOQ and pack sizes shown on the public site. Last updated ' . $ratesDate);
?>

<?php if ($error): ?>
    <div class="alert alert--error" style="margin-bottom:18px"><?= e($error) ?></div>
<?php endif; ?>

<div class="price-note" style="margin-bottom:18px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
    <span>Rates are shown publicly as <strong>indicative</strong>, excluding GST and freight. Save the change and update the "rates updated" date so buyers know the snapshot is current.</span>
</div>

<form method="post" action="/admin/products.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="rates">

    <div class="admin-panel">
        <div class="admin-panel-head">
            <h3 style="font-size:1rem">Grades</h3>
            <span class="small muted"><?= count($gradeRows) ?> rows</span>
        </div>
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Rate (₹/kg)</th>
                        <th>MOQ (kg)</th>
                        <th>Pack sizes</th>
                        <th>Badge</th>
                        <th>Live</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradeRows as $p): ?>
                        <tr>
                            <td>
                                <strong><?= e($p['name']) ?></strong>
                                <div class="sub"><?= e($p['grade_size']) ?> · <?= e($p['suta']) ?></div>
                                <div class="sub"><a href="/product/<?= e($p['slug']) ?>" target="_blank" rel="noopener">View page ↗</a></div>
                            </td>
                            <td>
                                <input type="number" name="rate[<?= (int)$p['id'] ?>]" value="<?= (int)$p['indicative_rate'] ?>"
                                    min="0" step="1" required
                                    style="width:110px;padding:8px 10px;border-radius:10px;border:1.5px solid var(--line-strong)">
                            </td>
                            <td>
                                <input type="number" name="moq[<?= (int)$p['id'] ?>]" value="<?= (int)$p['moq_kg'] ?>"
                                    min="1" step="1" required
                                    style="width:90px;padding:8px 10px;border-radius:10px;border:1.5px solid var(--line-strong)">
                            </td>
                            <td>
                                <input type="text" name="pack[<?= (int)$p['id'] ?>]" value="<?= e((string)$p['pack_sizes']) ?>"
                                    maxlength="255" style="min-width:220px;padding:8px 10px;border-radius:10px;border:1.5px solid var(--line-strong)">
                            </td>
                            <td>
                                <input type="text" name="badge[<?= (int)$p['id'] ?>]" value="<?= e((string)$p['badge']) ?>"
                                    maxlength="60" placeholder="optional" style="width:150px;padding:8px 10px;border-radius:10px;border:1.5px solid var(--line-strong)">
                            </td>
                            <td>
                                <label style="display:inline-flex;align-items:center;gap:7px;font-size:.86rem">
                                    <input type="checkbox" name="active[<?= (int)$p['id'] ?>]" value="1" <?= (int)$p['active'] === 1 ? 'checked' : '' ?>>
                                    Live
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Save rates</button>
        <a class="btn btn-ghost" href="/market" target="_blank" rel="noopener">Preview rate page</a>
    </div>
</form>

<?php admin_footer(); ?>