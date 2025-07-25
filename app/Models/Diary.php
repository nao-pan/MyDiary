<?php

namespace App\Models;

use App\Enums\EmotionState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Diary extends Model
{
    use HasFactory;



    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'user_id'
    ];

    /**
     * Get the user that owns the diary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emotionLogs()
    {
        return $this->hasMany(\App\Models\EmotionLog::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'diary_tag');
    }
    
}
