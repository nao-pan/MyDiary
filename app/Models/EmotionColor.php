<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmotionColor extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'emotion_state',
        'color_code',
    ];

    protected $casts = [
    'created_at' => 'datetime',
];
}
