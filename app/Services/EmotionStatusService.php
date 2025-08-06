<?php

namespace App\Services;

use App\Models\EmotionLog;
use App\Models\User;
use App\Enums\EmotionState;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use App\Dto\Chart\MonthlyEmotionBarChartData;
use App\Models\EmotionColor;

class EmotionStatusService
{
  /**
   * 感情の解禁状態を返す
   */
  public function buildEmotionStatuses(User $user, int $postCount): Collection
  {
    $unlokedEmotionStates = $user->unlockedEmotions()
      ->pluck('emotion_state')
      ->toArray();
    return collect(EmotionState::cases())->map(function ($emotion) use ($user, $unlokedEmotionStates, $postCount) {
      $isUnlocked = $emotion->isInitiallyUnlocked() || in_array($emotion->value, $unlokedEmotionStates);
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

      return (object)[
        'key' => $emotion->value,
        'label' => $emotion->label(),
        'color' => $emotion->defaultColor(),
        'text_color' => $emotion->textColor(),
        'unlocked' => $isUnlocked,
        'threshold' => $threshold,
        'unlockType' => $unlockType,
        'currentCount' => $currentCount,
        'remaining' => $remaining,
        'baseEmotion' => $baseEmotion?->label(),
        'is_initial' => $emotion->isInitiallyUnlocked(),
      ];
    });
  }

  /**
   * 直近の感情スコアを取得
   *
   * @param User $user
   * @param int $days
   * @return array
   */
  public function getRecentEmotionScores(User $user, int $days = 30): array
  {
    // 最近のEmotionLogを取得
    $recentLogs = EmotionLog::whereHas('diary', function ($query) use ($user) {
      $query->where('user_id', $user->id);
    })->where('created_at', '>=', Carbon::now()->subDays($days))
      ->get();

    // ベース感情の一覧を取得
    $baseEmotions = collect(EmotionState::cases())
      ->filter(fn($emotion) => $emotion->baseCategory() !== null)
      ->map(fn($emotion) => $emotion->baseCategory())
      ->unique();

    // 各ベース感情のスコアを集計
    foreach ($baseEmotions as $emotion) {
      $count = $recentLogs->filter(
        fn($log) =>
        $log->emotion_state->baseCategory() === $emotion
      )->count();

      $recentEmotionScores[] = $count;
    }

    return $recentEmotionScores;
  }
}
