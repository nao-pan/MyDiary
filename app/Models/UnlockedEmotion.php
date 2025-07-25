<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnlockedEmotion extends Model
{
    protected $fillable = ['user_id', 'emotion_state', 'diary_id', 'unlocked_at'];

    public function diary()
    {
        return $this->belongsTo(Diary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
