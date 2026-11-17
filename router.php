<?php
/**
 * Development router for PHP's built-in server only.
 * Emulates the Apache rewrite rules from .htaccess so local testing matches
 * production URLs. Never used on a real Apache/Nginx host.
 */

if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit('Not found');
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim($path, '/');
$file = realpath(__DIR__ . $path);

// ---- block internals, exactly like .htaccess ----
$blocked = ['/includes', '/data', '/config.php', '/config.sample.php', '/router.php'];
foreach ($blocked as $prefix) {
    if ($path === $prefix || str_starts_with($path, rtrim($prefix, '/') . '/')) {
        http_response_code(403);
        exit('Forbidden');
    }
}
if (preg_match('/\.(sql|lock|log|md)$/i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

// ---- directory index (Apache's DirectoryIndex behaviour) ----
if (is_dir(__DIR__ . $path)) {
    $index = __DIR__ . rtrim($path, '/') . '/index.php';
    if (is_file($index)) {
        require $index;
        return true;
    }
}

// ---- static files ----
if ($file && is_file($file)) {
    return false;
}

// ---- sitemap ----
if ($path === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    return true;
}

// ---- /product/{slug} ----
if (preg_match('#^/product/([a-z0-9\-]+)/?$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/product.php';
    return true;
}

// ---- /about -> about.php ----
if (preg_match('#^/([a-z0-9\-]+)/?$#', $path, $m) && is_file(__DIR__ . '/' . $m[1] . '.php')) {
    require __DIR__ . '/' . $m[1] . '.php';
    return true;
}

if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

http_response_code(404);
require __DIR__ . '/404.php';
return true;