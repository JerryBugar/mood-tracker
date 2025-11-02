<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * MoodQuote adalah model yang merepresentasikan kutipan-kutipan motivasi.
 * Model ini digunakan untuk menyimpan kutipan yang ditampilkan kepada pengguna.
 */
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
     * Handle the MoodQuote "created", "updated", and "deleted" events.
     * Menghapus cache kutipan ketika data diubah.
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
     * Invalidasi semua cache yang terkait dengan kutipan.
     * Ini dilakukan saat ada perubahan pada data kutipan.
     */
    private static function invalidateQuoteCache()
    {
        // Hapus cache acak untuk semua user (kita bisa buat lebih spesifik jika perlu)
        // Untuk sementara kita invalidasi dengan increment version
        Cache::increment('mood_quote_cache_version', 1, 0);
    }
    
    /**
     * Mendapatkan kunci cache dengan versi untuk kutipan acak.
     *
     * @param int|null $userId ID pengguna (opsional)
     * @return string Kunci cache yang unik
     */
    public static function getRandomQuoteCacheKey($userId = null)
    {
        $version = Cache::get('mood_quote_cache_version', 0);
        $suffix = $userId ? '_' . $userId : '';
        return 'random_mood_quote' . $suffix . '_v' . $version;
    }
    
    /**
     * Membersihkan cache kutipan untuk pengguna tertentu.
     *
     * @param int $userId ID pengguna
     */
    public static function clearUserQuoteCache($userId)
    {
        $cacheKey = self::getRandomQuoteCacheKey($userId);
        Cache::forget($cacheKey);
    }
    
    /**
     * Scope untuk mendapatkan kutipan secara acak.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRandom($query)
    {
        return $query->inRandomOrder();
    }
}
