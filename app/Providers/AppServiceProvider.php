<?php

namespace App\Providers;

use App\View\Components\Chart\PieChart;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('strlimit', function ($expression) {
            return "<?php echo \Illuminate\Support\Str::limit($expression); ?>";
        });
        Blade::component('chart.pie-chart', PieChart::class);
    }
}
