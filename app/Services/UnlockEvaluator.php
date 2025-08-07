<?php

namespace App\Services;

use App\Models\User;
use App\Rules\EmotionUnlockRule;
use App\Models\Diary;

class UnlockEvaluator
{
    public function isUnlocked(User $user, EmotionUnlockRule $rule): bool
    {
        if ($rule->isInitial()) {
            return true;
        }

        if ($rule->isPostCount()) {
            return $user->diaries()->count() >= $rule->threshold;
        }

        if ($rule->isBaseEmotion()) {
            $base = $rule->baseEmotion;
            return $base !== null &&
                   $user->diaries()
                        ->whereHas('emotionLog', fn($q) => $q->where('emotion_state', $base->value))
                        ->count() >= $rule->threshold;
        }

        if ($rule->isCombo()) {
            return $this->evaluateComboConditions($user, $rule->conditions);
        }

        return false;
    }

    protected function evaluateComboConditions(User $user, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            switch ($condition['type']) {
                case 'post_count':
                    if ($user->diaries()->count() < $condition['threshold']) {
                        return false;
                    }
                    break;

                case 'unlocked_emotions':
                    $unlocked = $user->unlockedEmotions()
                        ->pluck('emotion_state')
                        ->toArray();
                    if (!collect($condition['emotions'])->every(fn($e) => in_array($e, $unlocked))) {
                        return false;
                    }
                    break;

                case 'base_emotion':
                    $count = $user->diaries()
                        ->whereHas('emotionLog', fn($q) => $q->where('emotion_state', $condition['baseEmotion']))
                        ->count();
                    if ($count < $condition['threshold']) {
                        return false;
                    }
                    break;

                default:
                    return false; // 未対応条件タイプは失敗扱い
            }
        }

        return true;
    }
}
