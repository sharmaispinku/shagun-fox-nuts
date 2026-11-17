<?php
/**
 * About — origin, unit, and the grading discipline.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About — grading makhana in Purnea, Bihar | ' . SITE_NAME;
$pageDesc  = 'Shagun Enterprises is a makhana grading, packing and distribution business at Harda Bazar, Purnea, Bihar. Learn how our lots are sourced, graded and dispatched.';

$distRows = db()->query('SELECT * FROM distributors ORDER BY sort_order, id')->fetchAll();
$gradeRows = products();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">About</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="split">
            <div class="reveal">
                <span class="eyebrow">About us</span>
                <h1 style="font-size:clamp(2rem,3.8vw,3rem)">A makhana business built around the grade.</h1>
                <p class="lead" style="margin-top:18px">
                    Shagun Enterprises grades, packs and distributes makhana from Purnea — the district at the
                    centre of India's makhana belt. We work with growers around the Kosi–Mahananda ponds, buy at
                    the pond-side and finish every lot in our own unit on Kolasi Road.
                </p>
                <p style="margin-top:16px;color:var(--ink-soft)">
                    The work is unglamorous: sieve calibration, moisture checks, picking out split and broken
                    grains by hand, weighing to the gram before a bag is stitched. It is also the whole product.
                    A buyer who receives a consistent 16 mm lot six months apart is buying our discipline, not our
                    marketing.
                </p>

                <dl class="detail-list" style="margin-top:26px">
                    <dt>Registered name</dt><dd><?= e(CONFIG['site']['gstin'] ? 'Shagun Enterprises' : 'Shagun Enterprises') ?></dd>
                    <dt>GSTIN</dt><dd><?= e(CONFIG['site']['gstin'] ?? '—') ?></dd>
                    <dt>PAN</dt><dd><?= e(CONFIG['site']['pan'] ?? '—') ?></dd>
                    <dt>Unit</dt><dd><?= e(CONFIG['site']['address'] ?? '') ?></dd>
                    <dt>Grading lines</dt><dd><?= count($gradeRows) ?> calibrated grades, <?= e($gradeRows[0]['grade_size'] ?? '18 mm') ?> down to <?= e(end($gradeRows)['grade_size'] ?? '10 mm') ?></dd>
                    <dt>Dispatches</dt><dd><?= count($distRows) ?> distribution partners, pan-India road transport</dd>
                </dl>
            </div>

            <div class="reveal" style="display:flex;flex-direction:column;gap:18px">
                <div class="media-frame" style="aspect-ratio:4/3">
                    <?= picture('plant-howrah-2.jpg', 'Euryale ferox leaf and flower at the Indian Botanic Garden, Howrah', 1100, 825) ?>
                </div>
                <div class="media-frame" style="aspect-ratio:16/10">
                    <?= picture('biharr.jpg', 'Foxnuts being lightly roasted in Nawada district, Bihar', 1200, 900) ?>
                    <div class="media-badge">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3a5a40" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <div><strong>Roasted in Bihar</strong>Foxnuts being lightly roasted in Nawada district.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--warm">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">How we work</span>
            <h2>Six checks between the pond and your pallet.</h2>
        </div>
        <div class="process">
            <?php
            $steps = [
                ['Sourcing', 'Buying directly from growers around the Purnea–Kosi clusters during the season, with lot-level records.'],
                ['Drying & resting', 'Seed is dried and rested so moisture is stable before it is roasted and popped.'],
                ['Roasting & popping', 'Controlled roasting in small batches for even expansion and colour.'],
                ['Grading', 'Sieve-calibrated sizing into our four grades, followed by hand inspection for splits and stones.'],
                ['Packing', '25 / 50 kg PP bags, retail pouches or liners, labelled with grade, lot and net weight.'],
                ['Dispatch', 'Weight verified at loading, invoice and transport documents shared with the consignment.'],
            ];
            foreach ($steps as $i => $s): ?>
                <div class="step reveal" style="transition-delay: <?= $i * 50 ?>ms">
                    <h3><?= e($s[0]) ?></h3>
                    <p><?= e($s[1]) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">What we stand on</span>
            <h2>Four rules we do not bend.</h2>
        </div>
        <div class="grid grid-2">
            <?php
            $values = [
                ['Weight is net, always.', 'Bags are weighed after stitching. If a lot is short, we replace it — no arguments, no adjustments on the next invoice.'],
                ['The grade on the bag is the grade inside.', 'A 16 mm lot is not padded with 13 mm grain. Sizing is checked at packing, not just at intake.'],
                ['Quotes are lot-specific.', 'We do not hold a rate for a lot we have not seen. Indicative figures are labelled as such, everywhere.'],
                ['Documents travel with goods.', 'GST invoice, weight slip, batch details and LR copy are shared with every consignment, on request or by default.'],
            ];
            foreach ($values as $i => [$t, $d]): ?>
                <div class="card reveal" style="transition-delay: <?= $i * 60 ?>ms">
                    <h3><?= e($t) ?></h3>
                    <p style="margin-top:10px;color:var(--ink-soft);font-size:.94rem"><?= e($d) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($distRows): ?>
<section class="section section--paper">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Our partners</span>
            <h2>Distribution across Bihar.</h2>
            <p>These partners stock Shagun makhana and can serve you locally in their territory.</p>
        </div>
        <div class="grid grid-2">
            <?php foreach ($distRows as $d): ?>
                <div class="card reveal">
                    <h3 style="font-size:1.05rem"><?= e($d['name']) ?></h3>
                    <?php if (!empty($d['area']) || !empty($d['since'])): ?>
                        <p class="small muted" style="margin-top:6px"><?= e($d['area']) ?><?= $d['since'] ? ($d['area'] ? ' · ' : '') . 'Partner since ' . e($d['since']) : '' ?></p>
                    <?php endif; ?>
                    <?php if (!empty($d['note'])): ?>
                        <p class="small" style="margin-top:10px;color:var(--ink-soft)"><?= e($d['note']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="center" style="margin-top:24px"><a class="link-arrow" href="/distributors">Become a distribution partner →</a></p>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container">
        <div class="cta-inner">
            <div>
                <h2>See the lot before you buy.</h2>
                <p>Ask for a sample of any grade. Compare colour, size and roast, then decide on volume.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-gold btn-lg" href="/bulk-order?type=sample">Request a sample</a>
                <a class="btn btn-outline-light btn-lg" href="/contact">Visit our unit</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>