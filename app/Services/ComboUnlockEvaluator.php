<?php

namespace App\Services;
use App\Models\User;

class ComboUnlockEvaluator
{
    public function evaluate(User $user, array $conditions): array
    {
        $results = [];
        foreach ($conditions as $condition) {
            $results[] = $this->evaluateSingleCondition($user, $condition);
        }
        return $results;
    }

    private function evaluateSingleCondition(User $user, array $condition): bool
    {
        return match($condition['type']) {
            'post_count' => $user->diaries()->count() >= $condition['threshold'],
            'base_emotion' => $user->diaries()
                ->whereHas('emotionLog', fn($q) => $q->where('emotion_state', $condition['emotion']))
                ->count() >= $condition['threshold'],
            'emotion_unlocked' => $user->unlockedEmotions()
                ->whereIn('emotion_state', (array)$condition['emotions'])
                ->exists(),
        };
    }
}
