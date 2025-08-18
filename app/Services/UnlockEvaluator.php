<?php

namespace App\Services;

use App\Models\User;
use App\Rules\EmotionUnlockRule;

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
            $threshold = $rule->threshold;

            if ($base === null || ! is_numeric($threshold)) {
                return false;
            }

            return $user->diaries()
                ->whereHas('emotionLog', fn ($q) => $q->where('emotion_state', $base->value))
                ->count() >= $threshold;
        }

        if ($rule->isCombo()) {
            return $this->evaluateComboConditions($user, $rule->conditions);
        }

        return false;
    }

    protected function evaluateComboConditions(User $user, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? null;

            if (! $type) {
                return false;
            }

            switch ($type) {
                case 'post_count':
                    $threshold = $condition['threshold'] ?? null;
                    if (is_null($threshold) || $user->diaries()->count() < $threshold) {
                        return false;
                    }
                    break;

                case 'unlocked_emotions':
                    $required = $condition['emotions'] ?? [];
                    if (empty($required)) {
                        return false;
                    }

                    $unlocked = $user->unlockedEmotions()->pluck('emotion_state')->toArray();
                    if (! collect($required)->every(fn ($e) => in_array($e, $unlocked))) {
                        return false;
                    }
                    break;

                case 'base_emotion':
                    $baseEmotion = $condition['baseEmotion'] ?? null;
                    $threshold = $condition['threshold'] ?? null;

                    if (! $baseEmotion || is_null($threshold)) {
                        return false;
                    }

                    $count = $user->diaries()
                        ->whereHas('emotionLog', fn ($q) => $q->where('emotion_state', $baseEmotion))
                        ->count();

                    if ($count < $threshold) {
                        return false;
                    }
                    break;

                default:
                    return false; // 未対応条件タイプ
            }
        }

        return true;
    }
}
