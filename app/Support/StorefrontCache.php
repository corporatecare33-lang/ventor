<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for storefront data. Bumping the version invalidates every
 * key at once and works with any cache driver (file, database, redis).
 */
class StorefrontCache
{
    private const VERSION_KEY = 'storefront:version';

    public static function remember(string $key, Closure $callback, int $seconds = 3600): mixed
    {
        return Cache::remember(self::key($key), $seconds, $callback);
    }

    public static function key(string $key): string
    {
        return 'storefront:'.self::version().':'.$key;
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn () => 1);
    }
}
