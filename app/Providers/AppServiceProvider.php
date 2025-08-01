<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use App\Models\Diary;
use App\Policies\DiaryPolicy;
use App\View\Components\Chart\PieChart;

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
