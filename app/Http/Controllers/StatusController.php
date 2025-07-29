<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\EmotionState;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use App\Models\UnlockedEmotion;

class StatusController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $postCount = $user->diaries()->count();

        // 解禁済み感情（DBに記録されたもの）
        $unlockedEmotionStates = $user->unlockedEmotions()
            ->pluck('emotion_state')
            ->toArray();

        $statuses = EmotionState::cases();

        $emotionStatuses = collect($statuses)->map(function ($emotion) use ($user, $unlockedEmotionStates, $postCount) {
            $isUnlocked = $emotion->isInitiallyUnlocked() || in_array($emotion->value, $unlockedEmotionStates);
            $threshold = $emotion->unlockThreshold();
            $unlockType = $emotion->unlockType();

            $currentCount = 0;
            $remaining = null;
            $baseEmotion = null;

            if (!$isUnlocked && $threshold !== null) {
                if ($unlockType === 'post_count') {
                    $currentCount = $postCount;
                    $remaining = max(0, $threshold - $currentCount);

                } elseif ($unlockType === 'base_emotion') {
                    $baseEmotion = $emotion->unlockBaseEmotion();

                    if ($baseEmotion) {
                        $currentCount = $user->diaries()
                            ->whereHas('emotionLog', function ($query) use ($baseEmotion) {
                                $query->where('emotion_state', $baseEmotion->value);
                            })->count();

                        $remaining = max(0, $threshold - $currentCount);
                    }
                }
            }

            return [
                'label' => $emotion->label(),
                'color' => $emotion->color(),
                'unlocked' => $isUnlocked,
                'required' => $threshold,
                'base_emotion' => $baseEmotion?->label(),
                'current_count' => $currentCount,
                'remaining' => $remaining,
                'text_color' => $emotion->textColor(),
                'is_initial' => $emotion->isInitiallyUnlocked(),
                'unlock_type' => $unlockType,
            ];
        });

        return view('status.index', [
            'postCount' => $postCount,
            'emotionStatuses' => $emotionStatuses->sortByDesc('unlocked')->values(),
        ]);
    }
}
