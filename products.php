<?php
/**
 * Grades catalogue.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Makhana grades — 10 mm to 18 mm | ' . SITE_NAME;
$pageDesc  = 'Compare Shagun Enterprises makhana grades: Premium Select 18 mm, Superior 16 mm, Standard 13 mm and Regular 10 mm — with suta count, MOQ and indicative rates.';

$gradeRows = products();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Grades</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Grades</span>
            <h2>Choose the grain that fits your use.</h2>
            <p>Size drives everything downstream — roast time, coating pickup, pack appearance and price. Every grade below is sieve-calibrated and hand-checked before it leaves our unit.</p>
        </div>

        <div class="grid grid-2">
            <?php foreach ($gradeRows as $i => $p): ?>
                <article class="product-card reveal" style="transition-delay: <?= $i * 50 ?>ms">
                    <div class="product-media">
                        <?php if ($p['badge']): ?>
                            <span class="product-badge<?= $i === 0 ? ' product-badge--gold' : '' ?>"><?= e($p['badge']) ?></span>
                        <?php endif; ?>
                        <?= picture((string)$p['image'], $p['name'] . ' makhana', 900, 675) ?>
                    </div>
                    <div class="product-body">
                        <h3><?= e($p['name']) ?></h3>
                        <div class="product-specs">
                            <span class="spec-chip"><?= e($p['grade_size']) ?></span>
                            <span class="spec-chip"><?= e($p['suta']) ?></span>
                            <span class="spec-chip">MOQ <?= number_format((float)$p['moq_kg']) ?> kg</span>
                        </div>
                        <p><?= e((string)$p['description']) ?></p>
                        <div class="product-foot">
                            <div class="product-rate">
                                <?= inr($p['indicative_rate']) ?>/kg
                                <span>indicative</span>
                            </div>
                            <div style="display:flex;gap:14px;align-items:center">
                                <a class="link-arrow" href="/product/<?= e($p['slug']) ?>">Details</a>
                                <a class="btn btn-primary btn-sm" href="/bulk-order?grade=<?= e($p['slug']) ?>">Quote</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Side by side</span>
            <h2>Grade comparison.</h2>
        </div>

        <div class="table-wrap reveal">
            <table class="rate-table">
                <thead>
                    <tr>
                        <th scope="col">Grade</th>
                        <th scope="col">Grain size</th>
                        <th scope="col">Suta</th>
                        <th scope="col">Best for</th>
                        <th scope="col">MOQ</th>
                        <th scope="col">Indicative</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradeRows as $p): ?>
                        <tr>
                            <td><a href="/product/<?= e($p['slug']) ?>"><strong><?= e($p['name']) ?></strong></a></td>
                            <td><?= e($p['grade_size']) ?></td>
                            <td><?= e($p['suta']) ?></td>
                            <td><?= e(truncate((string)$p['tagline'], 52)) ?></td>
                            <td><?= number_format((float)$p['moq_kg']) ?> kg</td>
                            <td class="num"><?= inr($p['indicative_rate']) ?>/kg</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="price-note reveal" style="margin-top:20px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            <span>Rates are indicative wholesale references excluding GST and freight, and are confirmed per lot at the time of order.</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Not sure?</span>
            <h2>Ask for a sample.</h2>
            <p>Send us your city and the grade you are evaluating — we will share a sample lot with the lab-style grading sheet so you can compare before you commit.</p>
            <div class="hero-actions" style="justify-content:center">
                <a class="btn btn-primary" href="/bulk-order?type=sample">Request a sample</a>
                <a class="btn btn-ghost" href="<?= e(wa_link('Hello Shagun Enterprises, please share a makhana sample and price list.')) ?>" target="_blank" rel="noopener">Ask on WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>