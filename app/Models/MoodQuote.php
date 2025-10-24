<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
