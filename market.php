<?php
/**
 * Market rates — indicative wholesale reference table + rate drivers.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Makhana market rates — indicative wholesale | ' . SITE_NAME;
$pageDesc  = 'Indicative wholesale rate table for makhana grades from 10 mm to 18 mm, with MOQ and packing options. Rates confirmed per lot on order.';

$gradeRows = products();
$updated   = (string)setting('rates_updated', '—');

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Market rates</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Market</span>
            <h2>Indicative rates, updated <?= e($updated) ?>.</h2>
            <p>
                These are wholesale references for full-lot purchases ex-Purnea, excluding GST and freight.
                Makhana is a seasonal, grade-sensitive commodity — the rate you are quoted depends on the lot
                in front of you.
            </p>
        </div>

        <div class="table-wrap reveal">
            <table class="rate-table">
                <thead>
                    <tr>
                        <th scope="col">Grade</th>
                        <th scope="col">Grain size</th>
                        <th scope="col">Suta</th>
                        <th scope="col">MOQ</th>
                        <th scope="col">Indicative rate</th>
                        <th scope="col">Packing</th>
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
                            <td><?= e((string)$p['pack_sizes']) ?></td>
                            <td><a class="btn btn-ghost btn-sm" href="/bulk-order?grade=<?= e($p['slug']) ?>">Get quote</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="price-note reveal" style="margin-top:20px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
            <span>
                <strong>Indicative only.</strong> Rates are a planning reference, not an offer. They exclude GST
                (0% for whole makhana, applicable for processed/packed variants as per your invoice), freight,
                loading and any custom packing. Final rate is confirmed in writing against the specific lot.
            </span>
        </div>
    </div>
</section>

<section class="section section--paper">
    <div class="container">
        <div class="split">
            <div class="reveal">
                <span class="eyebrow">Quick estimate</span>
                <h2>What would my lot cost?</h2>
                <p class="lead" style="margin-top:12px">Pick a grade and quantity to see an indicative value. This is a rough planning figure only — your quote will be firmer and lot-specific.</p>

                <div class="card" style="margin-top:22px">
                    <div class="form-grid">
                        <div class="field">
                            <label for="grade">Grade</label>
                            <select id="grade">
                                <?php foreach ($gradeRows as $p): ?>
                                    <option value="<?= e($p['slug']) ?>" data-rate="<?= e($p['indicative_rate']) ?>">
                                        <?= e($p['name']) ?> — <?= e($p['grade_size']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="quantity_kg">Quantity (kg)</label>
                            <input type="number" id="quantity_kg" value="500" min="1" step="1" inputmode="numeric">
                        </div>
                    </div>
                    <div class="estimate">
                        <div>
                            <div class="label">Indicative value</div>
                            <div class="value" id="estimateValue">—</div>
                        </div>
                        <div class="sub" id="estimateSub">Excluding GST &amp; freight</div>
                    </div>
                    <div class="form-actions">
                        <a class="btn btn-primary" href="/bulk-order">Request a firm quote</a>
                        <span class="form-note">Volume breaks apply above 1,000 kg.</span>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <span class="eyebrow">What moves the rate</span>
                <h2>Five things that change your price.</h2>
                <ul class="stack" style="margin-top:18px">
                    <?php
                    $drivers = [
                        ['Grade and suta', 'A larger sieve size with a higher suta count means more hand-picking, more breakage loss and a higher rate.'],
                        ['Moisture at loading', 'Kebab-ready lots need low moisture. Drier stock costs more to produce but weighs honestly and stores better.'],
                        ['Season', 'Fresh crop arrivals soften rates; the lean months before the next harvest firm them up.'],
                        ['Packing', 'Bulk PP bags are cheapest per kg; printed retail pouches carry conversion and artwork cost.'],
                        ['Volume and distance', 'Full-lot and multi-lot orders absorb freight better than a single 25 kg bag.'],
                    ];
                    foreach ($drivers as $i => [$t, $d]): ?>
                        <li style="display:flex;gap:14px;align-items:flex-start">
                            <span class="spec-chip" style="flex-shrink:0"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <div>
                                <h4><?= e($t) ?></h4>
                                <p class="small muted" style="margin-top:3px"><?= e($d) ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Talk to the desk</span>
            <h2>Need today's rate for a specific lot?</h2>
            <p>Send the grade, quantity and city. We will check the current lot and revert with a firm figure.</p>
            <div class="hero-actions" style="justify-content:center">
                <a class="btn btn-primary" href="/bulk-order">Request a quote</a>
                <a class="btn btn-ghost" href="<?= e(wa_link('Hello Shagun Enterprises, please share today\'s makhana rates by grade.')) ?>" target="_blank" rel="noopener">WhatsApp the desk</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>