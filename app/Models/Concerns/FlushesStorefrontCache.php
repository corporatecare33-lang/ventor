<?php

namespace App\Models\Concerns;

use App\Support\StorefrontCache;

/**
 * Clears cached storefront fragments (homepage, menus, sitemap) whenever the model changes.
 */
trait FlushesStorefrontCache
{
    public static function bootFlushesStorefrontCache(): void
    {
        $flush = fn () => StorefrontCache::flush();

        static::saved($flush);
        static::deleted($flush);
    }
}
