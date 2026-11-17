<?php
/**
 * Terms of trade — website use and indicative rates.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Terms of trade | ' . SITE_NAME;
$pageDesc  = 'Terms covering use of this website, indicative pricing, quotations, orders and dispatch for Shagun Enterprises.';

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span><span aria-current="page">Terms</span>
    </nav>
</div>

<section class="section section--tight">
    <div class="container" style="max-width:820px">
        <span class="eyebrow">Terms</span>
        <h1 style="font-size:clamp(1.8rem,3.4vw,2.6rem)">Terms of trade</h1>
        <p class="lead" style="margin-top:14px">Last updated <?= e(date('F Y')) ?>.</p>

        <div class="stack" style="margin-top:28px;font-size:.96rem;color:var(--ink-soft)">
            <p>
                These terms cover the use of this website and the supply of makhana by <?= e(SITE_NAME) ?>
                (GSTIN <?= e(CONFIG['site']['gstin'] ?? '—') ?>). Placing an enquiry or order means you accept them.
            </p>

            <h3 style="margin-top:12px">1. Indicative rates</h3>
            <p>
                Prices shown on this site are indicative wholesale references for planning. They exclude GST,
                freight, loading and custom packing, and they are not an offer capable of acceptance. A binding
                price exists only in a written quotation issued against a specific lot.
            </p>

            <h3 style="margin-top:12px">2. Grades and description</h3>
            <p>
                Grades are described by sieve size and suta count. Photographs are representative of the grade
                and not of the exact lot supplied. Natural product varies in colour and size within the stated
                grade tolerance.
            </p>

            <h3 style="margin-top:12px">3. Orders and confirmation</h3>
            <p>
                An order is accepted only when we issue an order confirmation or proforma invoice. Minimum
                order quantities, packing and dispatch schedules are confirmed in that document.
            </p>

            <h3 style="margin-top:12px">4. Payment</h3>
            <p>
                Payment terms are stated on the invoice and agreed before dispatch. Goods are dispatched only
                after the agreed payment is received, unless a credit account has been approved in writing.
            </p>

            <h3 style="margin-top:12px">5. Weights and shortages</h3>
            <p>
                Weights are net and verified at loading. Any reported shortage or damage must be notified
                within 48 hours of delivery with photographs and the transporter's receipt, so we can settle
                it with the carrier.
            </p>

            <h3 style="margin-top:12px">6. Quality claims</h3>
            <p>
                Lots are supplied to the grade specification agreed at the time of order. Claims about moisture,
                foreign matter or grade must be raised with the batch details supplied with the consignment.
                Samples approved before dispatch are the reference standard.
            </p>

            <h3 style="margin-top:12px">7. Website content</h3>
            <p>
                Text, images and the Shagun Enterprises mark on this site belong to us or are used under licence.
                You may not copy or reuse them commercially without written permission.
            </p>

            <h3 style="margin-top:12px">8. Liability</h3>
            <p>
                Nothing here limits liability that cannot lawfully be limited. Otherwise, our liability for any
                claim relating to a consignment is limited to the invoice value of that consignment.
            </p>

            <h3 style="margin-top:12px">9. Governing law</h3>
            <p>These terms are governed by the laws of India, with courts in Purnea, Bihar having jurisdiction.</p>

            <p style="margin-top:22px">
                Questions? Write to
                <a href="mailto:<?= e(SITE_EMAIL) ?>" style="text-decoration:underline"><?= e(SITE_EMAIL) ?></a>
                or call <?= e(SITE_PHONE) ?>.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>