<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MoodQuote extends Model
{
    protected $table = 'mood_quotes'; // Spesifikasikan nama tabel

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote',
        'author',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Handle the MoodQuote "created" event.
     */
    protected static function booted()
    {
        static::created(function () {
            self::invalidateQuoteCache();
        });

        static::updated(function () {
            self::invalidateQuoteCache();
        });

        static::deleted(function () {
            self::invalidateQuoteCache();
        });
    }
    
    /**
     * Invalidate all quote-related cache
     */
    private static function invalidateQuoteCache()
    {
        // Hapus cache acak untuk semua user (kita bisa buat lebih spesifik jika perlu)
        $cacheKeys = Cache::getPrefix() . '*user_random_quote_*';
        // Kita tidak bisa langsung delete by pattern di semua driver cache, jadi kita gunakan pendekatan lain
        // Untuk sementara kita invalidasi dengan increment version
        Cache::increment('mood_quote_cache_version', 1, 0);
    }
    
    /**
     * Get cache key with version for random quote
     */
    public static function getRandomQuoteCacheKey($userId = null)
    {
        $version = Cache::get('mood_quote_cache_version', 0);
        $suffix = $userId ? '_' . $userId : '';
        return 'random_mood_quote' . $suffix . '_v' . $version;
    }
    
    /**
     * Clear specific user's quote cache
     */
    public static function clearUserQuoteCache($userId)
    {
        $cacheKey = self::getRandomQuoteCacheKey($userId);
        Cache::forget($cacheKey);
    }
}
