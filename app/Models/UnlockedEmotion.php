<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Testing\Fluent\Concerns\Has;

class UnlockedEmotion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'emotion_state',
        'diary_id',
        'unlocked_at'
    ];

    public function diary()
    {
        return $this->belongsTo(Diary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
