<?php
/**
 * Dynamic XML sitemap at /sitemap.xml (rewritten by .htaccess).
 */
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$urls = [
    ['/',            '1.0',  'weekly'],
    ['/products',    '0.9',  'weekly'],
    ['/market',      '0.9',  'daily'],
    ['/bulk-order',  '0.9',  'monthly'],
    ['/about',       '0.7',  'monthly'],
    ['/distributors','0.6',  'monthly'],
    ['/contact',     '0.7',  'monthly'],
    ['/faq',         '0.6',  'monthly'],
    ['/track',       '0.3',  'yearly'],
    ['/privacy',     '0.2',  'yearly'],
    ['/terms',       '0.2',  'yearly'],
];

foreach (products() as $p) {
    $urls[] = ['/product/' . $p['slug'], '0.8', 'weekly'];
}

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $priority, $freq]) {
    $lastmod = date('c');
    echo '  <url>' . "\n";
    echo '    <loc>' . e(SITE_URL . $loc) . "</loc>\n";
    echo '    <lastmod>' . substr($lastmod, 0, 10) . "</lastmod>\n";
    echo '    <changefreq>' . e($freq) . "</changefreq>\n";
    echo '    <priority>' . e($priority) . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";