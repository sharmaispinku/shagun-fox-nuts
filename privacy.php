<?php
/**
 * Privacy notice — what the quote form collects and why.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Privacy notice | ' . SITE_NAME;
$pageDesc  = 'How Shagun Enterprises handles the contact and order details you submit through this website.';

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Privacy</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container" style="max-width:820px">
        <span class="eyebrow">Privacy</span>
        <h1 style="font-size:clamp(1.8rem,3.4vw,2.6rem)">Privacy notice</h1>
        <p class="lead" style="margin-top:14px">Last updated <?= e(date('F Y')) ?>.</p>

        <div class="stack" style="margin-top:28px;font-size:.96rem;color:var(--ink-soft)">
            <p>
                This website is operated by <?= e(SITE_NAME) ?>, <?= e(CONFIG['site']['address'] ?? '') ?>.
                We collect only what we need to answer your enquiry and, if you order, to fulfil it.
            </p>

            <h3 style="margin-top:12px">What we collect</h3>
            <ul style="display:flex;flex-direction:column;gap:8px;padding-left:18px;list-style:disc">
                <li>Details you type into the quote or contact form — name, business name, phone, email, city, grade, quantity, packing preference, GSTIN and any note.</li>
                <li>Your IP address and browser user agent, stored as a hashed value to limit spam and abuse.</li>
                <li>Essential session cookies used to keep the form and security token working.</li>
            </ul>

            <h3 style="margin-top:12px">Why we collect it</h3>
            <p>
                To respond to your enquiry, prepare a quotation, arrange samples and dispatch, and meet our tax
                and accounting obligations. We do not sell, rent or trade your details.
            </p>

            <h3 style="margin-top:12px">Who sees it</h3>
            <p>
                Our grading and dispatch staff, and the accountant who raises your invoice. If you ask us to
                ship to you, the transporter receives your name, phone and delivery address — nothing more.
            </p>

            <h3 style="margin-top:12px">How long we keep it</h3>
            <p>
                Order records are retained for as long as tax law requires, and enquiries are pruned once they
                are clearly dead. You can ask us to delete an enquiry at any time.
            </p>

            <h3 style="margin-top:12px">Cookies</h3>
            <p>
                This site uses one essential session cookie and no advertising or analytics cookies. Embedded
                Google Maps may set its own cookies when you open the map on the contact page.
            </p>

            <h3 style="margin-top:12px">Your choices</h3>
            <p>
                To access, correct or delete the details we hold, email
                <a href="mailto:<?= e(SITE_EMAIL) ?>" style="text-decoration:underline"><?= e(SITE_EMAIL) ?></a>
                or call <?= e(SITE_PHONE) ?>. We will respond within a reasonable time and never charge for it.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>