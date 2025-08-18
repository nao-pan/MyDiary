<?php

namespace App\View\Components\Chart;

use App\Dto\Chart\EmotionPieChartData;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PieChart extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public EmotionPieChartData $pieChartData,
        public string $id = 'chart-pie',
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.chart.pie-chart');
    }
}
