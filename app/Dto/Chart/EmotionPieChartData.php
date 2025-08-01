<?php

namespace App\Dto\Chart;

class EmotionPieChartData
{
  public function __construct(
    public array $labels,
    public array $data,
    public array $backgroundColor = [],
  )
  {
    logger()->debug('[TRACE] EmotionPieChartData constructor called.', debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10));
  }

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