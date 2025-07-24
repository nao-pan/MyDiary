<?php
namespace App\Services;
use App\Models\EmotionLog;
use App\Enums\EmotionState;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class EmotionUnlockService
{
    public function unlockedEmotions(User $user): array
    {
        $counts = EmotionLog::where('user_id', $user->id)
                    ->select('emotion_state', DB::raw('count(*) as count'))
                    ->groupBy('emotion_state')
                    ->pluck('count', 'emotion_state');

        $unlocked = [
            EmotionState::HAPPY,
            EmotionState::SAD,
            EmotionState::FEAR,
            EmotionState::ANGRY,
        ];

        if (($counts['happy'] ?? 0) >= 10) {
            $unlocked[] = EmotionState::GRATEFUL;
        }
        if (($counts['sad'] ?? 0) >= 10) {
            $unlocked[] = EmotionState::MELANCHOLY;
        }

        return $unlocked;
    }
}
