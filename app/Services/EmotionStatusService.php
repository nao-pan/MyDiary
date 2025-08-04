<?php

namespace App\Services;

use App\Dto\Chart\EmotionPieChartData;
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

  public function getEmotionPieChartData(User $user): EmotionPieChartData
  {
    $labels = EmotionState::baseEmotions();
    $logs = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $user->id))->get();
    $counts = [];
    foreach (EmotionState::cases() as $emotion) {
      $base = $emotion->baseCategory();
      if ($base === null) continue;

      $label = $base->label();
      $counts[$label] ??= 0;
      $counts[$label] += $logs->where('emotion_state', $emotion->value)->count();
    }
    $labels = array_keys($counts);
    $data = array_values($counts);

    $userColors = EmotionColor::where('user_id', $user->id)
        ->whereIn('emotion_state', EmotionState::baseEmotions())
        ->get()
        ->mapWithKeys(fn($c) => [$c->emotion_state->label() => $c->color_code]);
    
    $backgroundColor = array_map(function($label) use ($userColors){
      $state = EmotionState::fromLabel($label);
      return $userColors[$label] ?? $state->defaultColor();
    }, $labels);
    return new EmotionPieChartData($labels, $data, $backgroundColor);
  }

  /**
   * 
   */
  public function getMonthlyChartData(User $user, int $month = 6): MonthlyEmotionBarChartData
  {
    $labels = $this->generateMonthLabels($month);
    $emotionCounts = $this->collectEmotionCountsByMonth($user, EmotionState::baseEmotions(), $labels);
    $datasets = $this->buildEmotionDatasets(EmotionState::baseEmotions(), $emotionCounts, count($labels));

        $options = [
        'responsive' => true,
        'scales' => [
            'x' => ['stacked' => true],
            'y' => ['stacked' => true],
        ],
        'plugins' => [
            'legend' => ['position' => 'bottom'],
        ],
    ];

    return new MonthlyEmotionBarChartData($labels, $datasets, $options);
  }

  public function getMonthlyEmotionStackedData(User $user, array $labels): array
  {
    $baseEmotions = [
      EmotionState::HAPPY,
      EmotionState::SAD,
      EmotionState::ANGRY,
      EmotionState::FEAR,
      EmotionState::SURPURISED,
      EmotionState::DISGUSTED,
    ];

    $emotionCounts = $this->collectEmotionCountsByMonth($user, $baseEmotions, $labels);
    $datasets = $this->buildEmotionDatasets($baseEmotions, $emotionCounts, count($labels));

    return [$labels, $datasets];
  }

  public function generateMonthLabels(int $months): array
  {
    $labels = [];
    for ($i = $months - 1; $i >= 0; $i--) {
      $labels[] = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i)->format('Y-m');
    }

    return array_values($labels);
  }

  private function collectEmotionCountsByMonth(User $user, array $baseEmotions, array $labels): array
  {
    $emotionCounts = [];

    foreach ($baseEmotions as $base) {
      $emotionCounts[$base->value] = array_fill_keys($labels, 0); // 月をキーに
    }
    foreach ($labels as $month) {
      [$year, $monthNum] = explode('-', $month);

      $logs = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $user->id))
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $monthNum)
        ->get();

      foreach ($logs as $log) {
        $emotion = $log->emotion_state;
        $base = $emotion->baseCategory();

        if ($base && isset($emotionCounts[$base->value][$month])) {
          $emotionCounts[$base->value][$month]++;
        }
      }
    }
    return $emotionCounts;
  }

  private function buildEmotionDatasets(array $baseEmotions, array $emotionCounts, int $labelCount): array
  {
    return collect($baseEmotions)->map(function ($emotion) use ($emotionCounts, $labelCount) {
      return [
        'label' => $emotion->label(),
        'data' => array_values($emotionCounts[$emotion->value] ?? array_fill(0, $labelCount, 0)),
        'backgroundColor' => $emotion->defaultColor(),
      ];
    })->toArray();
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
