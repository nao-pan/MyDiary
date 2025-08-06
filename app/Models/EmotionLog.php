<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\EmotionState;

class EmotionLog extends Model
{
    use HasFactory;
    
    // update_atが不要なため
    public $timestamps = false;

    protected $casts = [
        'emotion_state' => EmotionState::class, // 感情状態を列挙型としてキャスト
        'emotion_score' => 'decimal:1', // 小数点以下1桁の感情スコア
        'created_at' => 'datetime',
    ];

    protected $fillable = ['diary_id', 'emotion_state', 'emotion_score'];

    public function diary()
    {
        return $this->belongsTo(Diary::class);
    }
}
