<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Diary;
use App\Models\DailyMetric;
use App\Models\UserEvent;
use Carbon\Carbon;

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
