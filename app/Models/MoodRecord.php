<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * MoodRecord adalah model yang merepresentasikan catatan mood pengguna.
 * Setiap catatan mood berisi informasi tentang suasana hati pengguna,
 * alasan di balik mood tersebut, dan saran tindakan.
 */
class MoodRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mood',
        'reason',
        'suggestion_action',
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
     * Relasi: Satu MoodRecord dimiliki oleh satu User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk mengurutkan berdasarkan tanggal terbaru.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Accessor untuk mendapatkan label mood.
     *
     * @return string Label yang sesuai dengan kode mood
     */
    public function getMoodLabelAttribute(): string
    {
        $labels = [
            'netral' => 'Biasa saja',
            'senyum' => 'Senang',
            'sedih' => 'Sedih',
            'lelah' => 'Lelah',
            'marah' => 'Marah'
        ];

        return $labels[$this->mood] ?? $this->mood;
    }

    /**
     * Accessor untuk mendapatkan tanggal terformat.
     *
     * @return string Tanggal dalam format 'Hari, tanggal Bulan Tahun' dalam bahasa Indonesia
     */
    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->created_at)->locale('id_ID')->translatedFormat('l, j F Y');
    }

    /**
     * Accessor untuk mendapatkan waktu terformat.
     *
     * @return string Waktu dalam format 12 jam (AM/PM)
     */
    public function getFormattedTimeAttribute(): string
    {
        return Carbon::parse($this->created_at)->format('g:i A');
    }
}
