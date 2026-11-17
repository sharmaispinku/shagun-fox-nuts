<?php
/**
 * Track a request by reference + phone.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Track your request | ' . SITE_NAME;
$pageDesc  = 'Check the status of your makhana quote request using your reference number and the mobile number you submitted.';

$ref     = trim((string)($_POST['ref'] ?? $_GET['ref'] ?? ''));
$phoneIn = trim((string)($_POST['phone'] ?? ''));
$order   = null;
$error   = null;

if (is_post()) {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please reload and try again.';
    } elseif (rate_limited('track:' . hash('sha256', client_ip()), 15, 300)) {
        $error = 'Too many lookups. Please wait a few minutes and try again.';
    } else {
        $digits = preg_replace('/\D/', '', $phoneIn) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        $ref = strtoupper($ref);
        try {
            $stmt = db()->prepare('SELECT * FROM orders WHERE ref = ? LIMIT 1');
            $stmt->execute([$ref]);
            $row = $stmt->fetch();
        } catch (Throwable $e) {
            error_log('[shagun] track lookup failed: ' . $e->getMessage());
            $row = null;
            $error = 'Tracking is temporarily unavailable. Please call ' . SITE_PHONE . '.';
        }

        if ($row && hash_equals(hash('sha256', (string)$row['phone']), hash('sha256', $digits))) {
            $order = $row;
            record_rate_limit('track:' . hash('sha256', client_ip()));
        } elseif ($row) {
            // Do not reveal whether the reference exists.
            $error = 'We could not match that reference and mobile number. Please check both and try again.';
        } else {
            $error = 'We could not match that reference and mobile number. Please check both and try again.';
        }
    }
}

$flow     = ['new', 'contacted', 'quoted', 'confirmed', 'dispatched', 'completed'];
$flowText = [
    'new'        => 'Request received',
    'contacted'  => 'We reached out',
    'quoted'     => 'Quote shared',
    'confirmed'  => 'Order confirmed',
    'dispatched' => 'Dispatched',
    'completed'  => 'Delivered',
];

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Track request</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Track</span>
            <h2>Where is my request?</h2>
            <p>Enter the reference from your confirmation message and the mobile number you used. We never show order details to anyone else.</p>
        </div>

        <div class="track-card reveal">
            <form class="form-card" method="post" action="/track">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <div class="field">
                        <label for="ref">Reference number</label>
                        <input type="text" id="ref" name="ref" value="<?= e($ref) ?>" required maxlength="32"
                            placeholder="SHG-261117-4F2A" style="text-transform:uppercase;font-family:ui-monospace,Menlo,monospace">
                    </div>
                    <div class="field">
                        <label for="phone">Mobile number</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($phoneIn) ?>" required maxlength="14"
                            inputmode="numeric" data-phone-mask placeholder="98210 97272">
                    </div>
                </div>

                <div id="formMsg" style="margin-top:18px">
                    <?php if ($error): ?>
                        <div class="alert alert--error"><?= e($error) ?></div>
                    <?php elseif ($order): ?>
                        <div class="alert alert--success">
                            Request <strong><?= e($order['ref']) ?></strong> is currently
                            <strong><?= e(status_label((string)$order['status'])) ?></strong>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary btn-lg" type="submit">Check status</button>
                    <a class="btn btn-ghost btn-lg" href="<?= e(wa_link('Hello, I would like an update on my request.')) ?>" target="_blank" rel="noopener">Ask on WhatsApp</a>
                </div>
            </form>

            <?php if ($order): ?>
                <div class="card" style="margin-top:18px">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
                        <div>
                            <h3 style="font-size:1.05rem"><?= e($order['ref']) ?></h3>
                            <p class="small muted" style="margin-top:3px">
                                <?= e(order_types()[$order['type']] ?? $order['type']) ?> ·
                                Raised <?= e(fmt_datetime((string)$order['created_at'])) ?>
                            </p>
                        </div>
                        <span class="badge <?= e(status_class((string)$order['status'])) ?>"><?= e(status_label((string)$order['status'])) ?></span>
                    </div>

                    <dl class="detail-list" style="margin-top:18px">
                        <?php if (!empty($order['grade'])): ?>
                            <dt>Grade</dt><dd><?= e($order['grade']) ?></dd>
                        <?php endif; ?>
                        <?php if ((float)$order['quantity_kg'] > 0): ?>
                            <dt>Quantity</dt><dd><?= number_format((float)$order['quantity_kg']) ?> kg</dd>
                        <?php endif; ?>
                        <?php if (!empty($order['city'])): ?>
                            <dt>Delivery city</dt><dd><?= e($order['city']) ?><?= $order['state'] ? ', ' . e($order['state']) : '' ?></dd>
                        <?php endif; ?>
                        <?php if ((int)$order['estimate'] > 0): ?>
                            <dt>Indicative value</dt><dd><?= inr($order['estimate']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($order['updated_at']) && $order['status'] !== 'new'): ?>
                            <dt>Last update</dt><dd><?= e(fmt_datetime((string)$order['updated_at'])) ?></dd>
                        <?php endif; ?>
                    </dl>

                    <?php
                    $current = array_search((string)$order['status'], $flow, true);
                    if ($order['status'] === 'rejected' || $order['status'] === 'closed') {
                        $current = -1;
                    }
                    ?>
                    <?php if ($current !== false && $current >= 0): ?>
                        <ol class="timeline">
                            <?php foreach ($flow as $i => $key): ?>
                                <?php $cls = $i < $current ? 'is-done' : ($i === $current ? 'is-current' : ''); ?>
                                <li class="<?= $cls ?>">
                                    <span class="dot" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4"><path d="M20 6 9 17l-5-5"/></svg>
                                    </span>
                                    <h4><?= e($flowText[$key]) ?></h4>
                                    <p>
                                        <?php if ($i < $current): ?>
                                            Completed
                                        <?php elseif ($i === $current): ?>
                                            Current status
                                        <?php else: ?>
                                            Pending
                                        <?php endif; ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <div class="alert alert--info" style="margin-top:16px">
                            This request was closed. Call <?= e(SITE_PHONE) ?> if you would like to reopen it.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($order['admin_notes'])): ?>
                        <div class="alert alert--info" style="margin-top:16px">
                            <div>
                                <strong>Note from our desk</strong>
                                <div style="margin-top:4px;white-space:pre-line"><?= e($order['admin_notes']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>