<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\EmotionState;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
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

        $postCount = $user->diaries()->count();

        $emotionStatuses = collect($statuses)->map(function ($emotion) use ($user, $unlockedEmotionStates) {
            $isUnlocked = $emotion->isInitiallyUnlocked() || in_array($emotion->value, $unlockedEmotionStates);

            $threshold = $emotion->unlockThreshold();
            $baseEmotion = $emotion->unlockBaseEmotion();

            $currentCount = 0;
            $remaining = null;
            if(! $emotion->isInitiallyUnlocked() && $baseEmotion && $threshold !== null) {
                $currentCount = $user->diaries()
                    ->whereHas('emotionLogs', function ($query) use ($baseEmotion) {
                        $query->where('emotion_state', $baseEmotion->value);
                    })->count();
                $remaining = max(0, $threshold - $currentCount);
            }    
            // baseEmotionがnullの場合は、感情の解禁条件がまだないと仮定する
            $remaining = $baseEmotion && $threshold !== null
                ? max(0, $threshold - $currentCount)
                : null;
            return [
                'label' => $emotion->label(),
                'color' => $emotion->color(),
                'unlocked' => $isUnlocked,
                'required' => $threshold,
                'base_emotion' => $baseEmotion ? $baseEmotion->label() : null,
                'current_count' => $currentCount,
                'remaining' => $remaining,
                'text_color' => $emotion->textColor(),
                'is_initial' => $emotion->isInitiallyUnlocked(),
            ];
        });
        

        return view('status.index', [
            'postCount'=>$postCount,
            'emotionStatuses'=> $emotionStatuses->sortByDesc('unlocked')->values()
        ]);
    }

}
