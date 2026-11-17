<?php
/**
 * Contact — unit address, map, and a short enquiry form.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Contact — grading unit in Purnea, Bihar | ' . SITE_NAME;
$pageDesc  = 'Call, WhatsApp or write to Shagun Enterprises at Harda Bazar, Kolasi Road, Purnea 854303, Bihar. Grading desk open Monday to Saturday.';

$flashSuccess = flash('success');
$flashError   = flash('error');

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Contact</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Contact</span>
            <h2>Talk to the grading desk.</h2>
            <p>Call or WhatsApp for anything time-sensitive — lot availability, loading slots, rates. For documentation and everything else, the form below reaches the same desk.</p>
        </div>

        <div class="split">
            <div class="reveal" style="display:flex;flex-direction:column;gap:16px">
                <div class="card">
                    <h3>Unit address</h3>
                    <p style="margin-top:10px;color:var(--ink-soft)"><?= e(CONFIG['site']['address'] ?? '') ?></p>
                    <a class="link-arrow" style="margin-top:14px" href="<?= e(CONFIG['site']['map_link'] ?? '#') ?>" target="_blank" rel="noopener">
                        Open in Google Maps
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="grid grid-2">
                    <div class="card">
                        <h4>Phone / WhatsApp</h4>
                        <p style="margin-top:8px"><a class="link-arrow" href="<?= e(tel_link()) ?>"><?= e(SITE_PHONE) ?></a></p>
                        <p class="small muted" style="margin-top:6px"><?= e(CONFIG['site']['hours'] ?? '') ?></p>
                    </div>
                    <div class="card">
                        <h4>Email</h4>
                        <p style="margin-top:8px;word-break:break-word"><a class="link-arrow" href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a></p>
                        <p class="small muted" style="margin-top:6px">Replies within one working day</p>
                    </div>
                </div>

                <div class="card">
                    <h4>Registered details</h4>
                    <dl class="detail-list" style="margin-top:12px">
                        <dt>GSTIN</dt><dd><?= e(CONFIG['site']['gstin'] ?? '—') ?></dd>
                        <dt>PAN</dt><dd><?= e(CONFIG['site']['pan'] ?? '—') ?></dd>
                        <dt>Dispatch</dt><dd>Ex-Purnea, Bihar — pan-India road transport</dd>
                    </dl>
                </div>

                <div class="media-frame" style="aspect-ratio:16/10">
                    <?= picture('biharr.jpg', 'Makhana from Nawada district, Bihar', 1200, 900) ?>
                    <div class="media-badge">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3a5a40" stroke-width="1.8" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                        <div><strong>Purnea, Bihar</strong>At the centre of India's makhana belt.</div>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <form class="form-card" id="orderForm" action="/api/orders.php" method="post" novalidate data-phone="<?= e(SITE_PHONE) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="enquiry">
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <h3>Send a message</h3>
                    <p class="small muted" style="margin:6px 0 20px">Tell us what you need and we will route it to the right person.</p>

                    <div id="formMsg" style="margin-bottom:16px">
                        <?php if ($flashSuccess): ?>
                            <div class="alert alert--success"><?= e($flashSuccess) ?></div>
                        <?php elseif ($flashError): ?>
                            <div class="alert alert--error"><?= e($flashError) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Name <span class="req">*</span></label>
                            <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" autocomplete="name" required maxlength="120" placeholder="Full name">
                        </div>
                        <div class="field">
                            <label for="phone">Mobile / WhatsApp <span class="req">*</span></label>
                            <input type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel" inputmode="numeric" maxlength="14" data-phone-mask required placeholder="98210 97272">
                        </div>
                        <div class="field">
                            <label for="email">Email <span class="opt">(optional)</span></label>
                            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email" maxlength="160" placeholder="you@company.com">
                        </div>
                        <div class="field">
                            <label for="city">City <span class="opt">(optional)</span></label>
                            <input type="text" id="city" name="city" value="<?= e(old('city')) ?>" autocomplete="address-level2" maxlength="120" placeholder="Your city">
                        </div>
                        <div class="field field--full">
                            <label for="message">Message <span class="req">*</span></label>
                            <textarea id="message" name="message" maxlength="1500" required placeholder="How can we help?"><?= e(old('message')) ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <label class="consent" for="consent">
                            <input type="checkbox" id="consent" name="consent" value="1" required>
                            <span>You may contact me about this message. <span class="req">*</span></span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary btn-lg" type="submit">
                            Send message
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                        <a class="btn btn-ghost btn-lg" href="<?= e(wa_link('Hello Shagun Enterprises, I have an enquiry.')) ?>" target="_blank" rel="noopener">WhatsApp instead</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight section--paper">
    <div class="container">
        <div class="section-head reveal">
            <span class="eyebrow">Find us</span>
            <h2>Harda Bazar, Kolasi Road, Purnea.</h2>
            <p>Visits by appointment so someone from the grading floor is free to walk you through the lot.</p>
        </div>
        <div class="reveal" style="border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid var(--line);background:#fff">
            <div style="position:relative;padding-top:56.25%">
                <iframe
                    src="<?= e(CONFIG['site']['map_embed'] ?? '') ?>"
                    title="Map showing Shagun Enterprises, Harda Bazar, Purnea"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    style="position:absolute;inset:0;width:100%;height:100%;border:0"
                    allowfullscreen></iframe>
            </div>
        </div>
        <p class="small muted center" style="margin-top:14px">
            <?= e(CONFIG['site']['address'] ?? '') ?> ·
            <a class="link-arrow" href="<?= e(CONFIG['site']['map_link'] ?? '#') ?>" target="_blank" rel="noopener">Get directions</a>
        </p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>