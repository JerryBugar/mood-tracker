<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'message',
        'division',
        'scheduled_at',
        'target_user_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /**
     * Relasi: Satu Notification bisa dikirim ke banyak User
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_user')
                    ->withPivot('is_read', 'read_at')
                    ->withTimestamps();
    }
}
