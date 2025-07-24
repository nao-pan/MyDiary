<?php

namespace App\Models;

use App\Enums\EmotionState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diary extends Model
{
    use HasFactory;

    protected $casts = [
        'emotion_state' => EmotionState::class, // 感情状態を列挙型としてキャスト
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'emotion_state'
    ];

    /**
     * Get the user that owns the diary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiFeedback()
    {
        return $this->hasOne(AiFeedback::class);
    }

    public function emotionLogs()
    {
        return $this->hasMany(EmotionLog::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'diary_tag');
    }
    
}
