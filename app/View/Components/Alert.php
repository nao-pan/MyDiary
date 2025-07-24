<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    public $type; // Type of alert (e.g., success, error, info)
    /**
     * Create a new component instance.
     */
    public function __construct($type = 'info')
    {
        $this->type = $type; // Type of alert (e.g., success, error, info)
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert');
    }
}
