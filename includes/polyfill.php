<?php
/**
 * Minimal polyfills so the site runs on hosts without the mbstring extension.
 * Real mbstring functions are always preferred when available.
 */

declare(strict_types=1);

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int
    {
        $count = preg_match_all('/./us', $string);
        return $count === false ? strlen($string) : $count;
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr(string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            return substr($string, $start, $length ?? null);
        }
        $slice = $length === null ? array_slice($chars, $start) : array_slice($chars, $start, $length);
        return implode('', $slice);
    }
}

if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $string, ?string $encoding = null): string
    {
        return strtolower($string);
    }
}

if (!function_exists('mb_encode_mimeheader')) {
    function mb_encode_mimeheader(string $string, ?string $charset = null, ?string $transferEncoding = null, ?string $linefeed = null, int $indent = 0): string
    {
        return '=?UTF-8?B?' . base64_encode($string) . '?=';
    }
}