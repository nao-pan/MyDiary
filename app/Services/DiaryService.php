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
            // 日記の作成
            $diary = Diary::create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'content' => $data['content'],
            ]);

            // 感情ログの作成
            EmotionLog::create([
                'diary_id' => $diary->id,
                'emotion_state' => $data['emotion_state'],
                'score' => 1.0, // デフォルトスコア
                'created_at' => now(),
            ]);

            return $diary;
        });
    }
}
