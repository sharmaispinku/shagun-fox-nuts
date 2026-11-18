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

<a class="wa-float" href="<?= e(wa_link('Hello Shagun Enterprises, I would like to enquire about makhana.')) ?>" target="_blank" rel="noopener" aria-label="Chat with Shagun Enterprises on WhatsApp">
    <svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16.04 3.2a12.6 12.6 0 0 0-10.7 19.3L3.2 28.8l6.5-2.1a12.6 12.6 0 1 0 6.34-23.5Zm0 2.2a10.4 10.4 0 0 1 8.9 15.8l-.3.5.6 2.9-3-.9-.5.3a10.4 10.4 0 1 1-5.7-18.6Zm-3.3 4.4c-.2 0-.5.1-.7.3-.3.3-.9.9-.9 2.1 0 1.2.9 2.4 1 2.6.1.2 1.7 2.7 4.2 3.7 2.1.8 2.5.6 3 .6.5-.1 1.6-.7 1.8-1.3.2-.6.2-1.2.1-1.3-.1-.1-.3-.2-.6-.3l-1.9-.9c-.2-.1-.4-.1-.6.1l-.8 1c-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.5-1.6-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.6c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.6-.5h-.4Z"/></svg>
    <span class="wa-float__label">Chat on WhatsApp</span>
</a>

<div class="mobile-bar" role="navigation" aria-label="Quick contact">
    <a href="<?= e(tel_link()) ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M4 5c0 8.3 6.7 15 15 15l1.5-3.6-4-2-2 2a11 11 0 0 1-5.9-5.9l2-2-2-4L5 4.5Z"/></svg>
        <span>Call</span>
    </a>
    <a href="<?= e(wa_link('Hello Shagun Enterprises, I would like to enquire about makhana.')) ?>" target="_blank" rel="noopener">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.7 7.4L3 21l2.1-5.2A8.5 8.5 0 1 1 21 11.5Z"/></svg>
        <span>WhatsApp</span>
    </a>
    <a class="is-primary" href="/bulk-order">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M7 3h10l2 4-7 14L5 7l2-4Z"/></svg>
        <span>Get a quote</span>
    </a>
</div>

<a class="to-top" id="toTop" href="#" aria-label="Back to top">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</a>

<script src="<?= asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>