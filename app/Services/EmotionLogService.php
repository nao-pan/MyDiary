<?php

namespace App\Services;

use App\Models\EmotionLog;

class EmotionLogService
{
    /**
     * 感情ログを保存する
     *
     * @param array $data
     * @return \App\Models\EmotionLog
     */
    public function create(array $data): EmotionLog
    {
        return EmotionLog::create([
            'diary_id' => $data['diary_id'],
            'emotion_state' => $data['emotion_state'],
            'emotion_score' => $data['emotion_score'],
            'created_at' => $data['created_at'] ?? now(), // テスト用に上書き可能
        ]);
    }
}
