<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MoodRecord;

/**
 * User adalah model yang merepresentasikan pengguna aplikasi.
 * Model ini digunakan untuk autentikasi dan menyimpan informasi profil pengguna.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'division',
        'role',
        'jenis_kelamin',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi: Satu User memiliki banyak MoodRecord.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moodRecords(): HasMany
    {
        return $this->hasMany(MoodRecord::class);
    }

    /**
     * Scope untuk menemukan user yang terverifikasi.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope untuk menemukan user berdasarkan Google ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $googleId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGoogleId($query, string $googleId)
    {
        return $query->where('google_id', $googleId);
    }
}
