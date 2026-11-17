<?php
/**
 * 404 — friendly dead end with escape routes.
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

http_response_code(404);

$pageTitle = 'Page not found | ' . SITE_NAME;
$pageDesc  = 'That page is not here. Browse our makhana grades, market rates or contact the grading desk.';
$bodyClass = 'page-404';

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container center">
        <p class="eyebrow" style="justify-content:center">404</p>
        <h1 style="font-size:clamp(1.9rem,4vw,2.9rem)">This page has popped out of the lot.</h1>
        <p class="lead" style="margin:16px auto 0;max-width:46ch">
            The link may be old, or the page may have moved. Everything about our makhana is still one click away.
        </p>
        <div class="hero-actions" style="justify-content:center;margin-top:30px">
            <a class="btn btn-primary btn-lg" href="/">Back to home</a>
            <a class="btn btn-ghost btn-lg" href="/products">Browse grades</a>
            <a class="btn btn-ghost btn-lg" href="/contact">Contact us</a>
        </div>

        <div class="grid grid-3" style="margin-top:56px;text-align:left">
            <a class="card" href="/market">
                <h3 style="font-size:1rem">Indicative rates</h3>
                <p class="small muted" style="margin-top:6px">Current wholesale references by grade.</p>
            </a>
            <a class="card" href="/bulk-order">
                <h3 style="font-size:1rem">Request a quote</h3>
                <p class="small muted" style="margin-top:6px">Tell us grade, quantity and city.</p>
            </a>
            <a class="card" href="/track">
                <h3 style="font-size:1rem">Track a request</h3>
                <p class="small muted" style="margin-top:6px">Check status with your reference.</p>
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>