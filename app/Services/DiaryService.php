<?php

namespace App\Services;

use App\Models\Diary;
use App\Models\EmotionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiaryService
{
    public function createWithEmotion(array $data): Diary
    {
        return DB::transaction(function () use ($data) {
            $diary = Diary::create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'content' => $data['content'],
            ]);

            // AIなどによる感情分析の代わりに仮データを使う
            $emotion = [
                'emotion_state' => 'happy',
                'score' => 0.85,
            ];

            EmotionLog::create([
                'diary_id' => $diary->id,
                'emotion_state' => $emotion['emotion_state'],
                'score' => $emotion['score'],
            ]);

            return $diary;
        });
    }
}
