<?php

namespace App\Services;

use App\Dto\Chart\EmotionPieChartData;
use App\Dto\Chart\MonthlyEmotionBarChartData;
use App\Models\EmotionLog;
use App\Models\User;
use App\Enums\EmotionState;
use App\Models\EmotionColor;
use Illuminate\Support\Carbon;


/**
 * Chart.js用の感情グラフデータを生成するサービスクラス
 */
class EmotionChartService
{

  /**
   * 投稿感情の円グラフデータをDTOで返す
   */
  public function getEmotionPieChartData(User $user): EmotionPieChartData
  {
    $logs = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $user->id))->get();

    $baseEmotions = EmotionState::baseEmotions();
    $countsByBaseValue = array_fill_keys(array_map(fn($e) => $e->value, $baseEmotions), 0);

    foreach ($logs as $log) {
      $baseCategory = $log->emotion_state->baseCategory();
      if ($baseCategory) {
        $countsByBaseValue[$baseCategory->value]++;
      }
    }
    // ユーザーの感情色を取得
    $userColors = EmotionColor::where('user_id', $user->id)
      ->whereIn('emotion_state', array_map(fn($e) => $e->value, $baseEmotions))
      ->get()
      ->mapWithKeys(fn($c) => [$c->emotion_state->label() => $c->color_code]);

    $labels = array_map(fn($e) => $e->label(), $baseEmotions);
    $data = array_values($countsByBaseValue);

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

  /**
   * 月ラベルを生成するヘルパー
   */
  private function generateMonthLabels(int $months): array
  {
    $labels = [];
    for ($i = $months - 1; $i >= 0; $i--) {
      $labels[] = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i)->format('Y-m');
    }

    return array_values($labels);
  }

  /**
   * 月ごとの感情別投稿数を取得する
   */
  private function collectEmotionCountsByMonth(User $user, array $baseEmotions, array $labels): array
  {
     // ラベル→年月のセット
    $labelSet = array_flip($labels);

    // 期間全体を一括取得
    [$start, $end] = [Carbon::createFromFormat('Y-m', $labels[0])->startOfMonth(),
                      Carbon::createFromFormat('Y-m', end($labels))->endOfMonth()];

    $logs = EmotionLog::whereHas('diary', fn($q) => $q->where('user_id', $user->id))
        ->whereBetween('created_at', [$start, $end])
        ->get();

    // 初期化（base value => [ 'Y-m' => 0, ... ]）
    $result = [];
    foreach ($baseEmotions as $base) {
        $result[$base->value] = array_fill_keys($labels, 0);
    }

    foreach ($logs as $log) {
        $ym = $log->created_at->format('Y-m');
        if (!isset($labelSet[$ym])) continue;

        $base = $log->emotion_state->baseCategory();
        if ($base) {
            $result[$base->value][$ym]++;
        }
    }

    return $result;
  }

  /**
   * chart.jsのデータセットを構築するヘルパー
   */
  private function buildEmotionDatasets(array $baseEmotions, array $emotionCounts, int $labelCount): array
  {
    return array_map(function ($base) use ($emotionCounts, $labelCount) {
        $series = $emotionCounts[$base->value] ?? array_fill(0, $labelCount, 0);

        return [
            'label' => $base->label(),
            'data' => array_values($series),
            'backgroundColor' => $base->defaultColor(),
        ];
    }, $baseEmotions);
  }
}
