<?php

namespace App\Services;

use App\Dto\Chart\EmotionPieChartData;
use App\Dto\Chart\MonthlyEmotionBarChartData;
use App\Models\EmotionLog;
use App\Models\User;
use App\Enums\EmotionState;
use App\Models\EmotionColor;
use Illuminate\Support\Carbon;



class EmotionChartService
{

  /**
   * 投稿感情の円グラフデータをDTOで返す
   */
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

    $backgroundColor = array_map(function ($label) use ($userColors) {
      $state = EmotionState::fromLabel($label);
      return $userColors[$label] ?? $state->defaultColor();
    }, $labels);
    return new EmotionPieChartData($labels, $data, $backgroundColor);
  }


  /**
   * 月別感情棒グラフデータをDTOで返す
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

  private function generateMonthLabels(int $months): array
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
}
