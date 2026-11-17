<?php
/**
 * Shared footer + scripts. Closes <main> opened in header.php.
 */
if (!defined('SHAGUN_BOOTSTRAPPED')) {
    require_once __DIR__ . '/bootstrap.php';
}
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="brand brand--footer">
                    <img class="brand-mark" src="/assets/img/logo-mark.png" alt="" width="480" height="231">
                    <span class="brand-name">Shagun<small>Makhana</small></span>
                </div>
                <p>Grading, packing and bulk distribution of premium makhana from the Purnea belt of Bihar — supplying retailers, wholesalers, exporters and food businesses across India.</p>
                <div class="footer-actions">
                    <a class="btn btn-primary btn-sm" href="/bulk-order">Request a quote</a>
                    <a class="btn btn-outline-light btn-sm" href="<?= e(wa_link('Hello Shagun Enterprises, please share your current makhana price list.')) ?>" target="_blank" rel="noopener">WhatsApp us</a>
                </div>
                <p class="footer-gst">GSTIN <?= e(CONFIG['site']['gstin'] ?? '') ?> · PAN <?= e(CONFIG['site']['pan'] ?? '') ?></p>
            </div>

            <div class="footer-col">
                <h3>Grades</h3>
                <ul>
                    <?php foreach (products() as $p): ?>
                        <li><a href="/product/<?= e($p['slug']) ?>"><?= e($p['name']) ?> — <?= e($p['grade_size']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="/market">Current rates</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Company</h3>
                <ul>
                    <li><a href="/about">About us</a></li>
                    <li><a href="/distributors">Distributors</a></li>
                    <li><a href="/market">Market rates</a></li>
                    <li><a href="/bulk-order">Bulk order</a></li>
                    <li><a href="/track">Track request</a></li>
                    <li><a href="/faq">FAQs</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Reach us</h3>
                <ul class="footer-contact">
                    <li><a href="<?= e(tel_link()) ?>"><?= e(SITE_PHONE) ?></a></li>
                    <li><a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a></li>
                    <li><span><?= e(CONFIG['site']['address'] ?? '') ?></span></li>
                    <li><span><?= e(CONFIG['site']['hours'] ?? '') ?></span></li>
                </ul>
                <a class="footer-map-link" href="<?= e(CONFIG['site']['map_link'] ?? '#') ?>" target="_blank" rel="noopener">Open in Google Maps →</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
            <p class="footer-legal">
                <a href="/privacy">Privacy</a>
                <a href="/terms">Terms</a>
                <span>Indicative rates are indicative only and confirmed on order.</span>
            </p>
        </div>
    </div>
</footer>

<a class="to-top" id="toTop" href="#" aria-label="Back to top">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</a>

<script src="<?= asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>