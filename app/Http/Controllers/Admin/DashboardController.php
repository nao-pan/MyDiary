<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyMetric;
use App\Models\UserEvent;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 7);
        $metrics = DailyMetric::orderBy('date', 'desc')
            ->take($period === 'all' ? 365 : $period)
            ->get()
            ->reverse();

        $emotionDistribution = UserEvent::where('type', 'diary_posted')
            ->whereBetween('occurred_at', [now()->subDays($period === 'all' ? 365 : $period), now()])
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.emotionstate')) as emotion, COUNT(*) as count")
            ->groupBy('emotion')
            ->pluck('count', 'emotion');

        return view('admin.dashboard', compact('metrics', 'emotionDistribution', 'period'));
    }
}
