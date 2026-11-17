<?php
/**
 * Quote request — the main conversion page.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Request a quote — bulk makhana | ' . SITE_NAME;
$pageDesc  = 'Send your makhana requirement — grade, quantity, packing and destination city — and get a firm quote within one working day. Sample lots available.';

$gradeRows = products();
$types     = order_types();

$preGrade = trim((string)($_GET['grade'] ?? old('grade')));
$preType  = trim((string)($_GET['type'] ?? old('type', 'bulk')));
if (!array_key_exists($preType, $types)) {
    $preType = 'bulk';
}
if ($preGrade !== '' && !product($preGrade)) {
    $preGrade = '';
}

$flashError   = flash('error');
$flashSuccess = flash('success');
$received     = trim((string)($_GET['received'] ?? ''));

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Bulk order</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Bulk order</span>
            <h1 style="font-size:clamp(2rem,3.6vw,2.9rem)">Tell us what you need.</h1>
            <p>One form, one working day. Share the grade and quantity and our grading desk replies with a firm rate, packing options and the next loading date.</p>
        </div>

        <div class="split">
            <div class="reveal">
                <form class="form-card" id="orderForm" action="/api/orders.php" method="post" novalidate data-phone="<?= e(SITE_PHONE) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" id="typeField" value="<?= e($preType) ?>">
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div id="formMsg" style="margin-bottom:18px">
                        <?php if ($flashSuccess): ?>
                            <div class="alert alert--success"><?= e($flashSuccess) ?></div>
                        <?php elseif ($flashError): ?>
                            <div class="alert alert--error"><?= e($flashError) ?></div>
                        <?php elseif ($received !== ''): ?>
                            <div class="alert alert--success">
                                <strong>Request received.</strong> Your reference is <strong><?= e($received) ?></strong>.
                                Keep it handy — quote it on WhatsApp or while tracking your request.
                            </div>
                        <?php endif; ?>
                    </div>

                    <fieldset style="border:0;padding:0;margin:0 0 20px">
                        <legend style="font-size:.84rem;font-weight:600;margin-bottom:10px">I am enquiring about <span class="req">*</span></legend>
                        <div class="tag-row" id="typeChips" style="gap:10px">
                            <?php foreach ($types as $key => $label): ?>
                                <label class="type-chip" style="cursor:pointer">
                                    <input type="radio" name="type_choice" value="<?= e($key) ?>" <?= $key === $preType ? 'checked' : '' ?>
                                        style="position:absolute;opacity:0;width:0;height:0">
                                    <span class="tag" style="padding:9px 16px;font-size:.82rem;letter-spacing:0"><?= e($label) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Your name <span class="req">*</span></label>
                            <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required maxlength="120" placeholder="Full name">
                        </div>
                        <div class="field">
                            <label for="business_name">Business / firm name <span class="opt">(optional)</span></label>
                            <input type="text" id="business_name" name="business_name" value="<?= e(old('business_name')) ?>" autocomplete="organization" maxlength="160" placeholder="Shop, company or brand">
                        </div>
                        <div class="field">
                            <label for="phone">Mobile / WhatsApp <span class="req">*</span></label>
                            <input type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel" inputmode="numeric" maxlength="14" data-phone-mask required placeholder="98210 97272">
                            <span class="field-hint">10-digit Indian mobile — we reply faster on WhatsApp.</span>
                        </div>
                        <div class="field">
                            <label for="email">Email <span class="opt">(optional)</span></label>
                            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email" maxlength="160" placeholder="you@company.com">
                        </div>
                        <div class="field">
                            <label for="city">City / town <span class="req">*</span></label>
                            <input type="text" id="city" name="city" value="<?= e(old('city')) ?>" autocomplete="address-level2" maxlength="120" required placeholder="Delivery city">
                        </div>
                        <div class="field">
                            <label for="state">State <span class="opt">(optional)</span></label>
                            <input type="text" id="state" name="state" value="<?= e(old('state')) ?>" autocomplete="address-level1" maxlength="120" placeholder="Bihar">
                        </div>

                        <div class="field">
                            <label for="grade">Grade <span class="req">*</span></label>
                            <select id="grade" name="grade" required>
                                <option value="">Select a grade</option>
                                <?php foreach ($gradeRows as $p): ?>
                                    <option value="<?= e($p['slug']) ?>" data-rate="<?= e($p['indicative_rate']) ?>"
                                        <?= $p['slug'] === $preGrade ? 'selected' : '' ?>>
                                        <?= e($p['name']) ?> — <?= e($p['grade_size']) ?> (<?= e($p['suta']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="quantity_kg">Quantity (kg) <span class="req">*</span></label>
                            <div style="display:flex;gap:8px">
                                <button type="button" class="btn btn-ghost btn-sm" data-qty-step="quantity_kg" data-step="25" data-dir="down" aria-label="Reduce quantity by 25 kg" style="padding-inline:16px">−</button>
                                <input type="number" id="quantity_kg" name="quantity_kg" value="<?= e(old('quantity_kg', '')) ?>" min="1" max="200000" step="1" inputmode="numeric" placeholder="e.g. 500">
                                <button type="button" class="btn btn-ghost btn-sm" data-qty-step="quantity_kg" data-step="25" data-dir="up" aria-label="Increase quantity by 25 kg" style="padding-inline:16px">+</button>
                            </div>
                            <span class="field-hint">MOQ 25 kg per grade. Sample lots of 1–2 kg are fine.</span>
                        </div>

                        <div class="field">
                            <label for="packaging">Packing preference <span class="opt">(optional)</span></label>
                            <select id="packaging" name="packaging">
                                <option value="">Advise me</option>
                                <?php foreach (['25 kg PP bag', '50 kg PP bag', 'Retail pouches (100–500 g)', 'Custom printed liner', 'Buyer-supplied packing'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= old('packaging') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="timeline">Required by <span class="opt">(optional)</span></label>
                            <select id="timeline" name="timeline">
                                <option value="">Flexible</option>
                                <?php foreach (['Within 7 days', 'Within 2–3 weeks', 'This month', 'Next season planning'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= old('timeline') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="gstin">GSTIN <span class="opt">(optional — for a GST invoice)</span></label>
                            <input type="text" id="gstin" name="gstin" value="<?= e(old('gstin')) ?>" maxlength="15" placeholder="10GEIPK2689D1ZL" style="text-transform:uppercase">
                        </div>
                        <div class="field field--full">
                            <label for="message">Anything else we should know? <span class="opt">(optional)</span></label>
                            <textarea id="message" name="message" maxlength="1500" placeholder="Grade preference within a range, roast requirement, delivery timeline, pallet or loose loading…"><?= e(old('message')) ?></textarea>
                        </div>
                    </div>

                    <div class="estimate" id="estimateBox">
                        <div>
                            <div class="label">Indicative order value</div>
                            <div class="value" id="estimateValue">—</div>
                        </div>
                        <div class="sub" id="estimateSub">Enter quantity to see an estimate</div>
                    </div>

                    <div class="form-actions">
                        <label class="consent" for="consent">
                            <input type="checkbox" id="consent" name="consent" value="1" required>
                            <span>You may contact me on phone, WhatsApp or email about this enquiry. We never sell or share your details. <span class="req">*</span></span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary btn-lg" type="submit">
                            Send request
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                        <span class="form-note">No advance payment. No obligation.</span>
                    </div>
                </form>
            </div>

            <aside class="reveal" style="display:flex;flex-direction:column;gap:18px">
                <div class="card">
                    <h3>What happens next</h3>
                    <ol class="stack" style="margin-top:14px;font-size:.92rem;color:var(--ink-soft)">
                        <li><strong>1.</strong> We check stock for your grade and city.</li>
                        <li><strong>2.</strong> You get a firm quote on WhatsApp or email — usually the same working day.</li>
                        <li><strong>3.</strong> Approve a sample or book the lot; invoice follows against your GSTIN.</li>
                        <li><strong>4.</strong> We schedule loading and share transport details.</li>
                    </ol>
                </div>

                <div class="card">
                    <h3>Prefer to talk?</h3>
                    <p class="small muted" style="margin-top:8px">Our grading desk is on the line <?= e(CONFIG['site']['hours'] ?? 'Mon–Sat') ?>.</p>
                    <div class="stack" style="margin-top:14px">
                        <a class="btn btn-primary btn-block btn-sm" href="<?= e(wa_link('Hello Shagun Enterprises, I would like to discuss a bulk makhana requirement.')) ?>" target="_blank" rel="noopener">WhatsApp <?= e(SITE_PHONE) ?></a>
                        <a class="btn btn-ghost btn-block btn-sm" href="<?= e(tel_link()) ?>">Call <?= e(SITE_PHONE) ?></a>
                        <a class="btn btn-ghost btn-block btn-sm" href="mailto:<?= e(SITE_EMAIL) ?>">Email us</a>
                    </div>
                </div>

                <div class="price-note">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                    <span>Rates on this site are indicative. Your quote is confirmed for the specific lot, grade and packing you approve.</span>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="section section--paper section--tight">
    <div class="container">
        <div class="section-head section-head--center reveal">
            <span class="eyebrow">Buyer questions</span>
            <h2>Before you order.</h2>
        </div>
        <div class="faq-list">
            <?php
            $faqs = [
                ['What is the minimum order?', 'Wholesale lots start at 25 kg per grade, with 25 and 50 kg PP bags as standard packing. Sample lots of 1–2 kg are available so you can evaluate colour, size and roast behaviour first.'],
                ['How is the final rate decided?', 'Rate depends on grade size, suta count, moisture at loading, packing and volume. The figure on this site is an indicative wholesale reference; your quote is confirmed against the specific lot you approve.'],
                ['Do you provide GST invoices and transport documents?', 'Yes. We raise a GST invoice against your GSTIN and share the weight slip, batch details and e-way bill / LR copy with the consignment.'],
                ['Can you pack under our brand?', 'We can supply plain liners for your own packing, or pouch your approved lot in 100 g, 200 g and 500 g retail packs. Artwork and pouch specifications are agreed before production.'],
                ['Where do you dispatch from?', 'Our grading and packing unit is at Harda Bazar, Kolasi Road, Purnea, Bihar 854303. Road transport runs across Bihar, UP, Jharkhand, West Bengal, Delhi NCR and other states on request.'],
            ];
            foreach ($faqs as $i => [$q, $a]): ?>
                <div class="faq-item">
                    <button class="faq-q" aria-expanded="false" aria-controls="faq-order-<?= $i ?>" type="button">
                        <?= e($q) ?>
                        <span class="icon" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </button>
                    <div class="faq-a" id="faq-order-<?= $i ?>">
                        <div><p><?= e($a) ?></p></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="center" style="margin-top:26px"><a class="link-arrow" href="/faq">All FAQs →</a></p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>