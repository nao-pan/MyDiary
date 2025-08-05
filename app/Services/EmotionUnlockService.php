<?php

namespace App\Services;

use App\Models\EmotionLog;
use App\Enums\EmotionState;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UnlockedEmotion;
use Illuminate\Support\Facades\Auth;
use App\Models\Diary;

class EmotionUnlockService
{

    public function checkAndUnlock(Diary $diary): void
    {
        $userId = $diary->user_id;

        foreach (EmotionState::cases() as $emotion) {
            // 初期解禁感情はスキップ
            if ($emotion->isInitiallyUnlocked()) {
                continue;
            }

            // すでに解禁済みならスキップ
            $alreadyUnlocked = UnlockedEmotion::where('user_id', $userId)
                ->where('emotion_state', $emotion->value)
                ->exists();

            if ($alreadyUnlocked) {
                continue;
            }

            // 解除条件の種別（投稿数ベース or ベース感情ベース）を取得
            $unlockType = $emotion->unlockType();
            $required = $emotion->unlockThreshold();

            // 条件1：ベース感情による解除
            if ($unlockType === 'base_emotion') {
                $baseEmotion = $emotion->unlockBaseEmotion();
                if (is_null($baseEmotion) || is_null($required)) {
                    continue;
                }

                $count = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $userId))
                    ->where('emotion_state', $baseEmotion->value)
                    ->count();

                if ($count >= $required) {
                    $this->unlock($userId, $emotion, $diary->id);
                }

                // 条件2：投稿数のみで解除
            } elseif ($unlockType === 'post_count') {
                if (is_null($required)) {
                    continue;
                }

                $postCount = Diary::where('user_id', $userId)->count();
                if ($postCount >= $required) {
                    $this->unlock($userId, $emotion, $diary->id);
                }
            }
        }
    }

    public function getUnlockedEmotions(): array
    {
        return UnlockedEmotion::where('user_id', Auth::id())
            ->pluck('emotion_state')
            ->toArray();
    }
    protected function unlock(int $userId, EmotionState $emotion, int $diaryId): void
    {
        UnlockedEmotion::create([
            'user_id' => $userId,
            'emotion_state' => $emotion->value,
            'diary_id' => $diaryId,
            'unlocked_at' => now(),
        ]);
    }
}
