<?php

namespace App\Services;

use App\Models\EmotionLog;
use App\Models\User;
use App\Enums\EmotionState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class EmotionStatusService
{
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
        'emotion' => $emotion,
        'unlocked' => $isUnlocked,
        'threshold' => $threshold,
        'unlockType' => $unlockType,
        'currentCount' => $currentCount,
        'remaining' => $remaining,
        'baseEmotion' => $baseEmotion,
      ];
    });
  }

  public function getBaseEmotionChartData(User $user): array
  {
    $logs = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $user->id))->get();

    $counts = [];
    foreach (EmotionState::cases() as $emotion) {
      $base = $emotion->baseCategory();
      if ($base === null) continue;

      $label = $base->label();
      $counts[$label] ??= 0;
      $counts[$label] += $logs->where('emotion_state', $emotion->value)->count();
    }

    return $counts;
  }

  public function getMonthlyLabels(User $user, int $months = 6): array
  {
    return $this->getMonthlyEmotionData($user, $months)[0];
  }

  public function getMonthlyData(User $user, int $months = 6): array
  {
    return $this->getMonthlyEmotionData($user, $months)[1];
  }


  public function getMonthlyEmotionData(User $user, int $months = 6): array
  {
    $labels = [];
    $data = [];

    for ($i = $months - 1; $i >= 0; $i--) {
      $month = now()->subMonths($i)->format('Y-m');
      $labels[] = $month;

      $count = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $user->id))
        ->whereYear('created_at', Carbon::parse($month)->year)
        ->whereMonth('created_at', Carbon::parse($month)->month)
        ->count();

      $data[] = $count;
    }

    return [$labels, $data];
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
    $recentLogs = EmotionLog::where('user_id', $user->id)
      ->where('created_at', '>=', Carbon::now()->subDays($days))
      ->get();

    // ベース感情の一覧を取得
    $baseEmotions = collect(EmotionState::cases())
      ->filter(fn($emotion) => $emotion->baseCategory() !== null)
      ->map(fn($emotion) => $emotion->baseCategory())
      ->unique();

    // 各ベース感情のスコアを集計
    $recentEmotionScores = [];
    foreach ($baseEmotions as $emotion) {
      $count = $recentLogs->filter(function ($log) use ($emotion) {
        return EmotionState::from($log->emotion_state)->baseCategory() === $emotion;
      })->count();

      $recentEmotionScores[] = $count;
    }

    return $recentEmotionScores;
  }
}
