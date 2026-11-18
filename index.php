<?php
/**
 * Home — brand, grades, trading strengths and calls to action.
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Shagun Enterprises — Premium Makhana Grades from Purnea, Bihar';
$pageDesc  = 'Bulk supplier of graded makhana (fox nuts) from Purnea, Bihar. Four calibrated grades from 10 mm to 18 mm, packed and dispatched across India. Request a quote online.';

$gradeRows = products();
$distRows  = db()->query('SELECT * FROM distributors ORDER BY sort_order, id')->fetchAll();

$jsonLd = [[
    '@context'    => 'https://schema.org',
    '@type'       => 'ItemList',
    'name'        => 'Makhana grades',
    'itemListElement' => array_map(static function (array $p, int $i) {
        return [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => [
                '@type'    => 'Product',
                'name'     => $p['name'] . ' makhana — ' . $p['grade_size'],
                'sku'      => strtoupper($p['slug']),
                'category' => 'Makhana (fox nuts)',
                'material' => 'Euryale ferox',
                'url'      => SITE_URL . '/product/' . $p['slug'],
            ],
        ];
    }, $gradeRows, array_keys($gradeRows)),
]];

$homeFaqs = [
    ['What is the minimum order quantity?', 'Wholesale lots start at 25 kg per grade, in 25 kg or 50 kg PP bags. Sample lots of 1 to 2 kg are available so you can check colour, size and roast before committing to a bigger order.'],
    ['How do I get a quote?', 'Send the grade, quantity, packing preference and delivery city through the bulk order form, or message us on WhatsApp. Our grading desk replies with a firm rate and dispatch plan within one working day.'],
    ['Can I see a sample before ordering?', 'Yes. Ask for a sample of the grade you are evaluating — we share it with the grading details for that lot so you can compare against your current supplier.'],
    ['Do you provide a GST invoice?', 'Yes. Every consignment is invoiced against your GSTIN, with the weight slip and batch details shared alongside. Our GSTIN is 10GEIPK2689D1ZL.'],
    ['Where do you dispatch from?', 'Our grading and packing unit is at Harda Bazar, Kolasi Road, Purnea, Bihar 854303. We dispatch by road across India — Bihar, UP, Jharkhand, West Bengal, Delhi NCR and other states on request.'],
    ['Which grade should I choose?', 'Grade is decided by how the makhana will be used. Premium Select (18 mm, 7+ suta) suits premium retail and gifting, Superior (16 mm, 6+) is the everyday premium grade, Standard (13 mm, 5+) balances cost and appearance, and Regular (10 mm, 4+) works best for roasting, flavouring and further processing.'],
];

$jsonLd[] = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(static function (array $qa) {
        return [
            '@type'          => 'Question',
            'name'           => $qa[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
        ];
    }, $homeFaqs),
];

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-copy">
                <span class="eyebrow">From Purnea, Bihar — India's Makhana Belt</span>
                <h1>Premium makhana, <em>graded with care.</em></h1>
                <p class="lead">
                    Shagun Enterprises grades, packs and distributes makhana (fox nuts) sourced from the
                    ponds of the Purnea–Kosi belt. Four calibrated grades, honest weight, GST invoicing and
                    dispatch across India — from 25 kg sample lots to full truck loads.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="/products">
                        Explore grades
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a class="btn btn-ghost btn-lg" href="/bulk-order">Request a quote</a>
                </div>
                <ul class="hero-badges">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        Four calibrated grades (10–18 mm)
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        Bulk, retail and custom packing
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        GST invoice &amp; transport support
                    </li>
                </ul>
            </div>

            <div class="hero-visual">
                <div class="hero-card">
                    <img src="/assets/img/logo-full.png" alt="Shagun Enterprises makhana logo" width="1000" height="602" fetchpriority="high">
                </div>
                <?php foreach (array_slice($gradeRows, 0, 2) as $i => $p): ?>
                    <div class="hero-chip hero-chip--<?= $i === 0 ? 'tl' : 'br' ?>">
                        <span class="dot" aria-hidden="true"></span>
                        <div>
                            <strong><?= e($p['name']) ?></strong>
                            <span><?= e($p['grade_size']) ?> · <?= e($p['suta']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        <?php
        $items = ['Premium Select', 'Superior Grade', 'Standard Grade', 'Regular Grade', 'Bulk packing', 'GST invoice', 'Pan-India dispatch', 'Direct from Purnea'];
        foreach ([0, 1] as $pass): ?>
            <?php foreach ($items as $item): ?>
                <span class="marquee-item"><?= e($item) ?><span class="sep">✦</span></span>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>

<section class="stat-strip" aria-label="Company at a glance">
    <div class="container">
        <div class="stat-strip-inner">
            <div class="stat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 7l9-4 9 4-9 4-9-4Zm0 0v10l9 4 9-4V7"/></svg>
                <span class="num"><?= count($gradeRows) ?: 4 ?></span><span class="lbl">Calibrated grades</span>
            </div>
            <div class="stat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 21s7-5.5 7-11V5l-7-3-7 3v5c0 5.5 7 11 7 11Z"/></svg>
                <span class="num">10–18 mm</span><span class="lbl">Grain sizes</span>
            </div>
            <div class="stat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span class="num">Purnea</span><span class="lbl">Bihar origin</span>
            </div>
            <div class="stat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20 7h-9l-2 3H4a2 2 0 0 0 0 4h3l2 3h9a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/></svg>
                <span class="num">25 kg</span><span class="lbl">Minimum order</span>
            </div>
            <div class="stat">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/></svg>
                <span class="num">Pan-India</span><span class="lbl">Road dispatch</span>
            </div>
        </div>
    </div>
</section>

<section class="section" id="grades">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Our grades</span>
            <h2>Four grades, one grading discipline.</h2>
            <p>Every lot is sieved, hand-checked for split and broken grains, then weighed on calibrated scales. Choose by grain size, suta count and how the makhana will be used.</p>
        </div>

        <div class="grid grid-4">
            <?php foreach ($gradeRows as $i => $p): ?>
                <article class="product-card reveal" style="transition-delay: <?= $i * 60 ?>ms">
                    <div class="product-media">
                        <?php if ($p['badge']): ?>
                            <span class="product-badge<?= $i === 0 ? ' product-badge--gold' : '' ?>"><?= e($p['badge']) ?></span>
                        <?php endif; ?>
                        <?= picture((string)$p['image'], $p['name'] . ' makhana, ' . $p['grade_size'], 900, 675) ?>
                    </div>
                    <div class="product-body">
                        <h3><?= e($p['name']) ?></h3>
                        <div class="product-specs">
                            <span class="spec-chip"><?= e($p['grade_size']) ?></span>
                            <span class="spec-chip"><?= e($p['suta']) ?></span>
                        </div>
                        <p><?= e(truncate((string)$p['tagline'], 110)) ?></p>
                        <div class="product-foot">
                            <div class="product-rate">
                                <?= inr($p['indicative_rate']) ?>/kg
                                <span>indicative</span>
                            </div>
                            <a class="link-arrow" href="/product/<?= e($p['slug']) ?>">
                                Details
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="small muted reveal" style="margin-top:22px">
            Rates shown are indicative wholesale references and are confirmed on order — grain size, moisture,
            season and packing all move the final rate. <a class="link-arrow" href="/market">See the rate table →</a>
        </p>
    </div>
</section>

<section class="section section--warm">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Why buyers stay</span>
            <h2>A trading desk, not a middleman.</h2>
            <p>We buy at the pond-side, grade in our own unit and invoice directly — so the chain between the grower and your warehouse stays short and traceable.</p>
        </div>

        <div class="grid grid-3">
            <?php
            $features = [
                ['icon' => 'M3 7l9-4 9 4-9 4-9-4Zm0 0v10l9 4 9-4V7', 'title' => 'Direct pond-side sourcing', 'text' => 'Buying directly from growers around the Purnea–Kosi belt keeps origin clear and pricing honest through the season.'],
                ['icon' => 'M4 6h16M4 12h16M4 18h10', 'title' => 'Calibrated grading', 'text' => 'Sieve sizes are fixed per grade, so a 16 mm lot from us matches a 16 mm lot six months later.'],
                ['icon' => 'M12 3v18M5 8l7-5 7 5', 'title' => 'Moisture discipline', 'text' => 'Lots are dried and rested before packing, which protects shelf life and keeps weight stable in transit.'],
                ['icon' => 'M3 7h18v10H3zM7 12h.01', 'title' => 'Packing that fits', 'text' => '25/50 kg PP bags, retail pouches or your own branded liners — labelled with grade, lot and net weight.'],
                ['icon' => 'M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6z', 'title' => 'Documents in order', 'text' => 'GST invoices, e-way bills, weight slips and batch details shared with every consignment.'],
                ['icon' => 'M2 12h20M12 2v20', 'title' => 'Pan-India dispatch', 'text' => 'Road transport across Bihar, UP, Jharkhand, West Bengal, Delhi NCR and beyond, with tracking on request.'],
            ];
            foreach ($features as $i => $f): ?>
                <div class="card feature-card reveal" style="transition-delay: <?= $i * 50 ?>ms">
                    <div class="feature-ic">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($f['icon']) ?>"/></svg>
                    </div>
                    <h3><?= e($f['title']) ?></h3>
                    <p><?= e($f['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">How an order moves</span>
            <h2>From enquiry to dispatch.</h2>
        </div>
        <div class="process">
            <?php
            $steps = [
                ['Enquiry', 'Share grade, quantity, packing and destination city. We reply with a firm quote within a working day.'],
                ['Sample & approval', 'Ask for a 1–2 kg sample. Approve colour, size and roast behaviour before committing to a lot.'],
                ['Confirmation', 'Lock the rate, raise the invoice against your GSTIN and schedule the loading date.'],
                ['Dispatch', 'Goods leave our Purnea unit with weight slip, batch details and transport tracking.'],
            ];
            foreach ($steps as $i => $s): ?>
                <div class="step reveal" style="transition-delay: <?= $i * 60 ?>ms">
                    <h3><?= e($s[0]) ?></h3>
                    <p><?= e($s[1]) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="split">
            <div class="media-frame media-frame--tall reveal">
                <?= picture('plant-howrah-1.jpg', 'Euryale ferox, the makhana plant, at the Indian Botanic Garden in Howrah', 1100, 825, '', 'lazy') ?>
                <div class="media-badge">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3a5a40" stroke-width="1.8" aria-hidden="true"><path d="M12 22c0-6 3-10 8-11-1 6-4 9-8 11Zm0 0c0-6-3-10-8-11 1 6 4 9 8 11Z"/></svg>
                    <div><strong>Euryale ferox</strong>Makhana grows in still ponds across North Bihar.</div>
                </div>
            </div>
            <div class="reveal">
                <span class="eyebrow">Origin</span>
                <h2>Grown in Bihar's ponds, graded in Purnea.</h2>
                <p class="lead">
                    Makhana is the seed of <em>Euryale ferox</em>, a water lily cultivated in the shallow ponds and
                    wetlands of North Bihar. Growers collect the seed pods from the water, dry them, and roast and
                    pop the kernels — a craft measured in generations, not seasons.
                </p>
                <p style="margin-top:16px;color:var(--ink-soft)">
                    Our unit sits at Harda Bazar on the Kolasi Road, close to the growing clusters that supply us.
                    That proximity lets us inspect lots at source, reject what does not grade, and move confirmed
                    stock into packing the same week.
                </p>
                <ul class="pill-list" style="margin-top:24px">
                    <li>Purnea, Bihar</li>
                    <li>Kosi–Mahananda belt</li>
                    <li>Grower-direct buying</li>
                    <li>Own grading unit</li>
                </ul>
                <div class="hero-actions" style="margin-top:28px">
                    <a class="btn btn-primary" href="/about">More about us</a>
                    <a class="btn btn-ghost" href="/contact">Visit the unit</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Indicative rates</span>
            <h2>What makhana is trading at.</h2>
            <p>A reference snapshot for planning. Final rates depend on grade, moisture, packing and the volume you commit to.</p>
        </div>

        <div class="table-wrap reveal">
            <table class="rate-table">
                <thead>
                    <tr>
                        <th scope="col">Grade</th>
                        <th scope="col">Size</th>
                        <th scope="col">Suta</th>
                        <th scope="col">MOQ</th>
                        <th scope="col">Indicative rate</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradeRows as $p): ?>
                        <tr>
                            <td><strong><?= e($p['name']) ?></strong></td>
                            <td><?= e($p['grade_size']) ?></td>
                            <td><?= e($p['suta']) ?></td>
                            <td><?= number_format((float)$p['moq_kg']) ?> kg</td>
                            <td class="num"><?= inr($p['indicative_rate']) ?>/kg</td>
                            <td><a class="link-arrow" href="/product/<?= e($p['slug']) ?>">Details →</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="price-note reveal" style="margin-top:20px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            <span>
                Indicative wholesale reference only, excluding GST and freight. Updated <?= e(setting('rates_updated', '—')) ?>.
                <a href="/market" style="text-decoration:underline">See the full rate page</a> or
                <a href="/bulk-order" style="text-decoration:underline">request a firm quote</a>.
            </span>
        </div>
    </div>
</section>

<section class="section section--warm">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Three ways to buy</span>
            <h2>Pick the path that fits your business.</h2>
            <p>Same grading discipline, different packing and paperwork. Tell us which one you are and we will quote the lot accordingly.</p>
        </div>
        <div class="grid grid-3">
            <?php
            $paths = [
                [
                    'Wholesalers &amp; distributors',
                    'Graded bulk lots you can break and resell, with sizing that matches the last lot.',
                    ['25 / 50 kg PP bags with liner', 'Consistent sizing lot to lot', 'GST invoice and weight slip'],
                    'Bulk enquiry',
                ],
                [
                    'Retailers &amp; modern trade',
                    'Pouch-packed makhana for shelves, with batch codes and steady replenishment.',
                    ['100 g / 200 g / 500 g pouches', 'Batch and lot coding on every pack', 'Scheduled replenishment'],
                    'Retail packing',
                ],
                [
                    'Bulk &amp; institutional kitchens',
                    'Uniform grain for roasting lines, kheer, sweets and large kitchens.',
                    ['Uniform grain for even roasting', 'MOQ from 25 kg upward', 'Packed to your kitchen size'],
                    'Institutional supply',
                ],
            ];
            foreach ($paths as $i => [$title, $intro, $bullets, $cta]): ?>
                <div class="card path-card reveal" style="transition-delay: <?= $i * 60 ?>ms">
                    <h3><?= $title ?></h3>
                    <p style="margin-top:9px;color:var(--ink-soft);font-size:.93rem"><?= e($intro) ?></p>
                    <ul class="path-list">
                        <?php foreach ($bullets as $b): ?>
                            <li>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                <span><?= e($b) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="btn btn-ghost btn-sm btn-block" href="/bulk-order?type=bulk"><?= e($cta) ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">In the kitchen</span>
            <h2>One grain, plenty of uses.</h2>
            <p>Buyers pick a grade by how the makhana will be finished — dry-roasted and salted, tossed in masala, simmered into kheer, or blended into a trail mix.</p>
        </div>
        <div class="grid grid-3">
            <figure class="media-frame reveal" style="aspect-ratio:4/3.2;margin:0">
                <?= picture('masala.jpg', 'Roasted makhana tossed with masala and ghee', 900, 900) ?>
                <figcaption class="media-badge">
                    <div><strong>Roast &amp; masala</strong>Smaller, uniform grain coats evenly.</div>
                </figcaption>
            </figure>
            <figure class="media-frame reveal" style="aspect-ratio:4/3.2;margin:0">
                <?= picture('spiced.jpg', 'Spiced fox nuts (phool makhana) ready to serve', 900, 1260) ?>
                <figcaption class="media-badge">
                    <div><strong>Retail &amp; gifting</strong>Larger grain holds its shape and looks premium.</div>
                </figcaption>
            </figure>
            <figure class="media-frame reveal" style="aspect-ratio:4/3.2;margin:0">
                <?= picture('makhana-kheer.jpg', 'Makhana kheer, the classic Indian preparation', 1100, 616) ?>
                <figcaption class="media-badge">
                    <div><strong>Kheer &amp; curries</strong>Mid-size grain softens without collapsing.</div>
                </figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Origin &amp; craft</span>
            <h2>From the pond to the pack.</h2>
            <p>
                Makhana begins as a seed inside a prickly pod on a floating leaf. What happens next — drying,
                roasting, popping, grading — decides the price and the plate. These frames are representative of
                the crop, the plant and the finished snack.
            </p>
        </div>

        <div class="gallery">
            <figure class="reveal">
                <?= picture('biharr.jpg', 'Foxnuts being lightly roasted in Nawada district, Bihar', 1200, 900) ?>
                <figcaption>
                    <strong>Roasting the seed</strong>
                    Foxnuts being lightly roasted — Nawada district, Bihar.
                </figcaption>
            </figure>
            <figure class="reveal">
                <?= picture('plant-howrah-3.jpg', 'Euryale ferox growing at the Indian Botanic Garden, Howrah', 1100, 825) ?>
                <figcaption>
                    <strong>The makhana plant</strong>
                    <em>Euryale ferox</em> — prickly waterlily, Indian Botanic Garden, Howrah.
                </figcaption>
            </figure>
            <figure class="reveal">
                <?= picture('plant-nandankanan.jpg', 'Prickly waterlily leaf at Nandankanan, Odisha', 1100, 825) ?>
                <figcaption>
                    <strong>Under the leaf</strong>
                    The fruit ripens underwater; each pod holds the seed.
                </figcaption>
            </figure>
        </div>
    </div>
</section>

<?php if ($distRows): ?>
<section class="section section--tight">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Distribution</span>
            <h2>Stocked by partners across Bihar.</h2>
        </div>
        <div class="grid grid-4">
            <?php foreach ($distRows as $i => $d): ?>
                <div class="card center reveal" style="transition-delay: <?= $i * 40 ?>ms">
                    <h3 style="font-size:1.02rem"><?= e($d['name']) ?></h3>
                    <?php if (!empty($d['area'])): ?>
                        <p class="small muted" style="margin-top:6px"><?= e($d['area']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="center small muted" style="margin-top:20px">
            Want to stock Shagun makhana in your area? <a class="link-arrow" href="/distributors">Become a distributor →</a>
        </p>
    </div>
</section>
<?php endif; ?>

<section class="section section--paper">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Before you order</span>
            <h2>The questions every buyer asks.</h2>
            <p>Still unsure about something? The grading desk answers on WhatsApp within working hours.</p>
        </div>

        <div class="faq-list">
            <?php foreach ($homeFaqs as $i => [$q, $a]): ?>
                <div class="faq-item reveal">
                    <button class="faq-q" aria-expanded="false" aria-controls="home-faq-<?= $i ?>" type="button">
                        <?= e($q) ?>
                        <span class="icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </button>
                    <div class="faq-a" id="home-faq-<?= $i ?>">
                        <div><p><?= e($a) ?></p></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div class="cta-inner">
            <div>
                <h2>Ready to book a lot?</h2>
                <p>Tell us the grade, quantity and city — we will come back with a firm quote, packing options and a dispatch date.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-gold btn-lg" href="/bulk-order">Request a quote</a>
                <a class="btn btn-outline-light btn-lg" href="<?= e(wa_link('Hello Shagun Enterprises, I would like a quote for makhana.')) ?>" target="_blank" rel="noopener">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>