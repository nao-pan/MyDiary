<?php

namespace App\Dto\Chart;

class EmotionPieChartData
{
  public function __construct(
    public array $labels,
    public array $data,
    public array $backgroundColor = [],
  )
  {}

  public function toArray(): array
  {
    return [
      'labels' => $this->labels,
      'datasets' => [[
                  'data' => $this->data,
                  'backgroundColor' => $this->backgroundColor,
      ]],
      'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ]
                ]
            ]
    ];
  }
}