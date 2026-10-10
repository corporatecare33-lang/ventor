<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    /** Format an amount in Bangladeshi Taka, e.g. ৳2,450 */
    function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;

        return '৳'.number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);
    }
}

if (! function_exists('normalize_phone')) {
    /** Normalise Bangladeshi mobile numbers to 01XXXXXXXXX (other formats are returned digits-only). */
    function normalize_phone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }
}

if (! function_exists('clean_html')) {
    /**
     * Allow-list sanitiser for rich text written in the admin panel (descriptions, pages, blog).
     * Strips scripts, iframes, event-handler attributes and javascript: URLs.
     */
    function clean_html(?string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h2><h3><h4><blockquote><hr><figure><figcaption><img><table><thead><tbody><tr><th><td><span><code><pre>';
        $html = strip_tags((string) $html, $allowed);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*(javascript|vbscript|data):[^"\'>\s]*/i', '$1=$2#', $html);

        return $html;
    }
}
