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

            // ベース感情を Enum 側で定義していると仮定して、その投稿数を数える
            $baseEmotion = $emotion->unlockBaseEmotion();
            $required = $emotion->unlockThreshold();

            if (is_null($baseEmotion) || is_null($required)) {
                continue;
            }

            $count = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $userId))
                               ->where('emotion_state', $baseEmotion->value)
                               ->count();

            if ($count >= $required) {
                UnlockedEmotion::create([
                    'user_id' => $userId,
                    'emotion_state' => $emotion->value,
                    'diary_id' => $diary->id,
                    'unlocked_at' => now(),
                ]);
            }
        }
    }
    public function getUnlockedEmotions(): array
    {
        return UnlockedEmotion::where('user_id', Auth::id())
                              ->pluck('emotion_state')
                              ->toArray();
    }
}
