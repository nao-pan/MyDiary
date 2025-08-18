<?php

namespace App\View\Components\Chart;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BarChart extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $labels,
        public array $datasets,
        public string $id = 'chart-bar',
        public ?array $options = null
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.chart.bar-chart');
    }
}
