<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmotionLog extends Model
{
    use HasFactory;

    protected $fillable = ['diary_id', 'emotion_state', 'score'];

    public function diary()
    {
        return $this->belongsTo(Diary::class);
    }
}
