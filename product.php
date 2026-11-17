<?php
/**
 * Single grade detail — served at /product/{slug} via .htaccess rewrite.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$p    = $slug !== '' ? product($slug) : null;

if (!$p) {
    http_response_code(404);
    $pageTitle = 'Grade not found | ' . SITE_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container center">'
        . '<h1 style="font-size:2rem">That grade is not listed.</h1>'
        . '<p class="lead" style="margin:14px auto;max-width:44ch">It may have been renamed or retired. '
        . 'Browse all current grades or ask us directly.</p>'
        . '<div class="hero-actions" style="justify-content:center"><a class="btn btn-primary" href="/products">All grades</a>'
        . '<a class="btn btn-ghost" href="/contact">Contact us</a></div></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$features = product_features($p);
$related  = array_values(array_filter(products(), static fn(array $r): bool => $r['slug'] !== $p['slug']));

$pageTitle = $p['name'] . ' makhana ' . $p['grade_size'] . ' — ' . SITE_NAME;
$pageDesc  = truncate(strip_tags(($p['tagline'] ?? '') . ' ' . ($p['description'] ?? '')), 155);
$pageImage = '/assets/img/' . $p['image'];

$jsonLd = [[
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $p['name'] . ' makhana — ' . $p['grade_size'],
    'sku'         => strtoupper((string)$p['slug']),
    'category'    => 'Makhana (fox nuts)',
    'description' => $pageDesc,
    'image'       => SITE_URL . $pageImage,
    'material'    => 'Euryale ferox',
    'brand'       => ['@type' => 'Brand', 'name' => SITE_NAME],
    'additionalProperty' => [
        ['@type' => 'PropertyValue', 'name' => 'Grain size', 'value' => $p['grade_size']],
        ['@type' => 'PropertyValue', 'name' => 'Suta', 'value' => $p['suta']],
        ['@type' => 'PropertyValue', 'name' => 'Minimum order', 'value' => number_format((float)$p['moq_kg']) . ' kg'],
    ],
]];

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span>
        <a href="/products">Grades</a><span>/</span>
        <span aria-current="page"><?= e($p['name']) ?></span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="split">
            <div class="media-frame reveal" style="aspect-ratio:1/1">
                <?= picture((string)$p['image'], $p['name'] . ' makhana, ' . $p['grade_size'], 900, 900, '', 'eager') ?>
                <?php if (!empty($p['badge'])): ?>
                    <span class="product-badge product-badge--gold"><?= e($p['badge']) ?></span>
                <?php endif; ?>
            </div>

            <div class="reveal">
                <span class="eyebrow"><?= e($p['grade_size']) ?> · <?= e($p['suta']) ?></span>
                <h1><?= e($p['name']) ?></h1>
                <p class="lead" style="margin-top:14px"><?= e((string)$p['tagline']) ?></p>
                <p style="margin-top:14px;color:var(--ink-soft)"><?= e((string)$p['description']) ?></p>

                <div class="tag-row" style="margin-top:20px">
                    <span class="tag">MOQ <?= number_format((float)$p['moq_kg']) ?> kg</span>
                    <span class="tag"><?= e($p['grade_size']) ?></span>
                    <span class="tag"><?= e($p['suta']) ?></span>
                    <span class="tag">GST invoice</span>
                </div>

                <div class="product-rate" style="margin-top:24px;font-size:1.8rem">
                    <?= inr($p['indicative_rate']) ?>/kg
                    <span style="font-size:.7rem">Indicative wholesale rate · excluding GST &amp; freight</span>
                </div>

                <div class="hero-actions" style="margin-top:26px">
                    <a class="btn btn-primary btn-lg" href="/bulk-order?grade=<?= e($p['slug']) ?>">Request a quote</a>
                    <a class="btn btn-ghost btn-lg" href="<?= e(wa_link('Hello Shagun Enterprises, I am interested in ' . $p['name'] . ' makhana (' . $p['grade_size'] . '). Please share your best rate and availability.')) ?>" target="_blank" rel="noopener">WhatsApp</a>
                </div>

                <p class="small muted" style="margin-top:16px">
                    Dispatch from Purnea, Bihar. Packing in 25 / 50 kg PP bags or custom liners. Updated <?= e(setting('rates_updated', '—')) ?>.
                </p>
            </div>
        </div>
    </div>
</section>

<?php if ($features): ?>
<section class="section section--paper">
    <div class="container">
        <div class="split split--reverse">
            <div class="reveal">
                <span class="eyebrow">Specification</span>
                <h2>What is in the lot.</h2>
                <ul class="stack" style="margin-top:20px">
                    <?php foreach ($features as $f): ?>
                        <li style="display:flex;gap:12px;align-items:flex-start">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3a5a40" stroke-width="2.4" style="flex-shrink:0;margin-top:4px" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <span style="color:var(--ink-soft)"><?= e($f) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card reveal">
                <h3>Packing options</h3>
                <ul class="stack small" style="margin-top:14px;color:var(--ink-soft)">
                    <li><strong>25 / 50 kg</strong> — standard PP woven bags with liner, labelled with grade, lot and net weight.</li>
                    <li><strong>Retail pouches</strong> — 100 g / 200 g / 500 g stand-up pouches, your artwork or ours.</li>
                    <li><strong>Bulk liners</strong> — custom printed liners for onward packing at your facility.</li>
                </ul>
                <div class="price-note" style="margin-top:18px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                    <span>Weights are net and verified at loading. Photos are representative of the grade, not the exact lot.</span>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Other grades</span>
            <h2>Compare with.</h2>
        </div>
        <div class="grid grid-3">
            <?php foreach (array_slice($related, 0, 3) as $i => $r): ?>
                <article class="product-card reveal" style="transition-delay: <?= $i * 50 ?>ms">
                    <div class="product-media">
                        <?= picture((string)$r['image'], $r['name'] . ' makhana', 900, 675) ?>
                    </div>
                    <div class="product-body">
                        <h3 style="font-size:1.1rem"><?= e($r['name']) ?></h3>
                        <div class="product-specs">
                            <span class="spec-chip"><?= e($r['grade_size']) ?></span>
                            <span class="spec-chip"><?= e($r['suta']) ?></span>
                        </div>
                        <div class="product-foot">
                            <div class="product-rate"><?= inr($r['indicative_rate']) ?>/kg<span>indicative</span></div>
                            <a class="link-arrow" href="/product/<?= e($r['slug']) ?>">View →</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="cta-band">
    <div class="container">
        <div class="cta-inner">
            <div>
                <h2>Want <?= e($p['name']) ?> in bulk?</h2>
                <p>Share your quantity and city. We will confirm availability, rate and the next loading date.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-gold btn-lg" href="/bulk-order?grade=<?= e($p['slug']) ?>">Request a quote</a>
                <a class="btn btn-outline-light btn-lg" href="<?= e(wa_link('Hello Shagun Enterprises, I want to order ' . $p['name'] . ' makhana.')) ?>" target="_blank" rel="noopener">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>