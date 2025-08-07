<?php

namespace App\Services;

use App\Models\EmotionLog;
use App\Enums\EmotionState;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UnlockedEmotion;
use Illuminate\Support\Facades\Auth;
use App\Models\Diary;
use App\Rules\UnlockRuleRepository;

class EmotionUnlockService
{

    public function __construct(
        protected UnlockEvaluator $unlockEvaluator,
        protected UnlockRuleRepository $unlockRuleRepository
    ){}

    public function checkAndUnlock(Diary $diary): void
    {
        $user = $diary->user;

        foreach ($this->unlockRuleRepository->all() as $rule) {
            if ($rule->isInitial()) {
                continue; // 初期解禁はスキップ
            }
            if ($this->unlockEvaluator->isUnlocked($user, $rule)) {
                $already = $user->unlockedEmotions()
                    ->where('emotion_state', $rule->emotion->value)
                    ->exists();

                    if (!$already) {
                    $this->unlock($user->id, $rule->emotion, $diary->id);
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
