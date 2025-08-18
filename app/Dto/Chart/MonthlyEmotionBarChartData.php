<?php

namespace App\Dto\Chart;

class MonthlyEmotionBarChartData
{
    public function __construct(
        public array $labels,
        public array $datasets,
        public array $options = []
    ) {}

    public function toArray(): array
    {
        return [
            'labels' => $this->labels,
            'datasets' => $this->datasets,
            'options' => $this->options,
        ];
    }
}
