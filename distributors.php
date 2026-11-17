<?php
/**
 * Distributors — existing partners and the pitch for new ones.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Distributors — stock Shagun makhana | ' . SITE_NAME;
$pageDesc  = 'Become a distribution partner for Shagun Enterprises makhana. Territory support, consistent grading and reliable dispatch from Purnea, Bihar.';

$distRows = db()->query('SELECT * FROM distributors ORDER BY sort_order, id')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Distributors</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Distribution</span>
            <h2>Stocked by partners across Bihar.</h2>
            <p>Our partner network keeps Shagun makhana available locally, so buyers can inspect, collect and reorder without waiting on a long-haul dispatch.</p>
        </div>

        <div class="grid grid-2">
            <?php foreach ($distRows as $i => $d): ?>
                <div class="card reveal" style="transition-delay: <?= $i * 50 ?>ms">
                    <div style="display:flex;align-items:flex-start;gap:16px">
                        <div class="feature-ic" style="margin-bottom:0;flex-shrink:0">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:1.05rem"><?= e($d['name']) ?></h3>
                            <?php if (!empty($d['area']) || !empty($d['since'])): ?>
                                <p class="small muted" style="margin-top:4px"><?= e($d['area']) ?><?= $d['since'] ? ($d['area'] ? ' · ' : '') . 'Since ' . e($d['since']) : '' ?></p>
                            <?php endif; ?>
                            <?php if (!empty($d['note'])): ?>
                                <p class="small" style="margin-top:8px;color:var(--ink-soft)"><?= e($d['note']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!$distRows): ?>
            <div class="empty">Partner list is being updated. Call <?= e(SITE_PHONE) ?> for your nearest stockist.</div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--warm">
    <div class="container">
        <div class="split">
            <div class="reveal">
                <span class="eyebrow">Become a partner</span>
                <h2>Own a territory. We will back it.</h2>
                <p class="lead" style="margin-top:14px">
                    We are adding distribution partners in districts where makhana demand is growing but
                    consistent supply is not. Partners get protected territory, a fixed grade mix and direct
                    access to the grading desk.
                </p>
                <ul class="stack" style="margin-top:20px">
                    <?php
                    $perks = [
                        ['Territory protection', 'Your district is marked out and not double-covered without a conversation.'],
                        ['Consistent grade mix', 'Standing allocation across our four grades so your shelves do not empty mid-season.'],
                        ['Ready documentation', 'GST invoicing, weight slips and batch details on every consignment.'],
                        ['Marketing support', 'Photos, grade specs and pack shots you can use in your own catalogue and on WhatsApp.'],
                        ['Credit discussion', 'Payment terms discussed case by case after the first consignment.'],
                    ];
                    foreach ($perks as [$t, $d]): ?>
                        <li style="display:flex;gap:12px;align-items:flex-start">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3a5a40" stroke-width="2.4" style="flex-shrink:0;margin-top:4px" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <div>
                                <h4><?= e($t) ?></h4>
                                <p class="small muted" style="margin-top:2px"><?= e($d) ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="reveal">
                <div class="card">
                    <h3>Apply to become a partner</h3>
                    <p class="small muted" style="margin-top:8px">Share your district and the kind of buyer base you serve — retail, wholesale or HoReCa.</p>
                    <div class="hero-actions" style="margin-top:18px;flex-direction:column;align-items:stretch">
                        <a class="btn btn-primary btn-block" href="/bulk-order?type=distributor">Apply online</a>
                        <a class="btn btn-ghost btn-block" href="<?= e(wa_link('Hello Shagun Enterprises, I would like to apply as a distribution partner. My district is:')) ?>" target="_blank" rel="noopener">Apply on WhatsApp</a>
                        <a class="btn btn-ghost btn-block" href="<?= e(tel_link()) ?>">Call <?= e(SITE_PHONE) ?></a>
                    </div>
                </div>

                <div class="price-note" style="margin-top:18px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                    <span>We appoint partners on a first-consignment basis. No franchise fee; you buy at the same wholesale rate as any trade buyer.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div class="cta-inner">
            <div>
                <h2>Looking for a local stockist?</h2>
                <p>Tell us your city and we will point you to the nearest partner or dispatch direct from Purnea.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-gold btn-lg" href="/bulk-order">Find supply</a>
                <a class="btn btn-outline-light btn-lg" href="<?= e(wa_link('Hello, I am looking for a stockist near my city.')) ?>" target="_blank" rel="noopener">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>