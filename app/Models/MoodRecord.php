<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodRecord extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel jika tidak mengikuti konvensi 'mood_records'.
     * Jika Anda menamai tabel 'mood_records', baris ini tidak wajib.
     */
    // protected $table = 'mood_records';

    /**
     * Kolom yang boleh diisi secara massal.
     * Pastikan ini cocok dengan nama di form dan validasi.
     */
    protected $fillable = [
        'user_id',
        'mood',
        'reason',
        'suggestion_action',
    ];

    /**
     * Relasi: Satu MoodRecord dimiliki oleh satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
