<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CacheService
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'articles:';

    public function getCachedArticles(string $cacheKey): mixed
    {
        $fullKey = $this->buildKey($cacheKey);
        \Log::info('Attempting to get from cache', ['key' => $fullKey]);

        $value = Redis::get($fullKey);
        \Log::info('Cache result', [
            'key' => $fullKey,
            'found' => !is_null($value)
        ]);

        return $value ? json_decode($value, true) : null;
    }

    public function cacheArticles(string $cacheKey, $data, ?int $ttl = null): void
    {
        try {
            $fullKey = $this->buildKey($cacheKey);
            \Log::info('Attempting to cache data', [
                'key' => $fullKey,
                'ttl' => $ttl ?? self::CACHE_TTL,
                'data_type' => gettype($data),
                'data_size' => is_array($data) ? count($data) : 'not_array'
            ]);

            Redis::setex(
                $fullKey,
                $ttl ?? self::CACHE_TTL,
                json_encode($data)
            );

            // Verify the data was stored
            $stored = Redis::get($fullKey);
            \Log::info('Cache verification', [
                'key' => $fullKey,
                'stored_successfully' => !is_null($stored)
            ]);

        } catch (\Exception $e) {
            Log::error('Cache storage failed', [
                'error' => $e->getMessage(),
                'key' => $cacheKey,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function invalidateCache(string $cacheKey): void
    {
        $fullKey = $this->buildKey($cacheKey);
        \Log::info('Invalidating cache', ['key' => $fullKey]);
        Redis::del($fullKey);
    }

    public function buildKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }

    public function buildFilterKey(array $filters): string
    {
        ksort($filters);
        return md5(serialize($filters));
    }
}
