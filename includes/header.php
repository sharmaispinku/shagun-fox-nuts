<?php
/**
 * Shared document head + site header.
 *
 * Pages set these before including this file:
 *   $pageTitle, $pageDesc, $pageImage (optional), $bodyClass (optional),
 *   $jsonLd (optional array or list of arrays)
 */
if (!defined('SHAGUN_BOOTSTRAPPED')) {
    require_once __DIR__ . '/bootstrap.php';
}

$pageTitle = $pageTitle ?? SITE_NAME . ' — Premium Makhana Grades from Purnea, Bihar';
$pageDesc  = $pageDesc  ?? 'Shagun Enterprises grades, packs and distributes premium makhana (fox nuts) from Purnea, Bihar. Four calibrated grades, bulk supply across India.';
$pageImage = $pageImage ?? '/assets/img/hero-makhana.jpg';
$bodyClass = $bodyClass ?? '';
$canonical = SITE_URL . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$jsonLd    = $jsonLd ?? [];

$orgLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => SITE_NAME,
    'url'      => SITE_URL,
    'logo'     => SITE_URL . '/assets/img/logo-full.png',
    'image'    => SITE_URL . '/assets/img/logo-full.png',
    'description' => 'Processor and bulk supplier of graded makhana (fox nuts) from Purnea, Bihar.',
    'telephone' => SITE_PHONE,
    'email'     => SITE_EMAIL,
    'address'   => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Harda Bazar, Kolasi Road, Harda',
        'addressLocality' => 'Purnea',
        'addressRegion'  => 'Bihar',
        'postalCode'     => '854303',
        'addressCountry' => 'IN',
    ],
    'contactPoint' => [[
        '@type' => 'ContactPoint',
        'telephone' => SITE_PHONE,
        'contactType' => 'sales',
        'areaServed' => 'IN',
        'availableLanguage' => ['en', 'hi'],
    ]],
];

$allLd = array_merge([$orgLd], is_array($jsonLd) ? $jsonLd : [$jsonLd]);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="theme-color" content="#3a5a40">
<meta name="robots" content="index,follow,max-image-preview:large">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDesc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(SITE_URL . $pageImage) ?>">
<meta property="og:locale" content="en_IN">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($pageDesc) ?>">
<meta name="twitter:image" content="<?= e(SITE_URL . $pageImage) ?>">

<link rel="icon" href="/assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/favicon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/favicon-16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="/assets/favicon/site.webmanifest">

<link rel="preload" as="image" type="image/webp" href="/assets/img/logo-full.png">
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/fraunces-normal-400_700.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/assets/fonts/inter-normal-400_700.woff2" crossorigin>
<link rel="stylesheet" href="<?= asset('/assets/css/fonts.css') ?>">
<link rel="stylesheet" href="<?= asset('/assets/css/main.css') ?>">

<?php foreach ($allLd as $ld): ?>
<script type="application/ld+json"><?= json_attr($ld) ?></script>
<?php endforeach; ?>
</head>
<body<?= $bodyClass ? ' class="' . e($bodyClass) . '"' : '' ?>>

<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar">
    <div class="container topbar-inner">
        <p class="topbar-note">
            <span class="pulse" aria-hidden="true"></span>
            Indicative rates updated <?= e(setting('rates_updated', '—')) ?>
        </p>
        <div class="topbar-links">
            <a href="<?= e(tel_link()) ?>"><?= e(SITE_PHONE) ?></a>
            <span class="sep" aria-hidden="true">·</span>
            <a href="<?= e(wa_link('Hello Shagun Enterprises, I would like to enquire about bulk makhana supply.')) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <span class="sep" aria-hidden="true">·</span>
            <span><?= e(CONFIG['site']['hours'] ?? '') ?></span>
        </div>
    </div>
</div>

<header class="site-header" id="siteHeader">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= e(SITE_NAME) ?> — home">
            <img class="brand-mark" src="/assets/img/logo-mark.png" alt="" width="480" height="231">
            <span class="brand-name">Shagun<small>Makhana</small></span>
        </a>

        <nav class="nav" id="primaryNav" aria-label="Primary">
            <?php foreach (nav_items() as $item): ?>
                <a href="<?= e($item['href']) ?>"<?= nav_active($item['href']) ?>><?= e($item['label']) ?></a>
            <?php endforeach; ?>
            <a href="/track"<?= nav_active('/track') ?>>Track</a>
            <a class="btn btn-primary btn-sm nav-cta" href="/bulk-order">Get a quote</a>
        </nav>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="primaryNav">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main id="main">