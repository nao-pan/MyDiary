<?php

namespace App\Console\Commands;

use App\Models\DailyMetric;
use App\Models\User;
use App\Models\UserEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildDailyMetrics extends Command
{
    protected $signature = 'metrics:build-daily';

    protected $description = '前日分のKPIを集計してdaily_metricsに保存';

    public function handle(): int
    {
        // 集計対象日（前日）
        $date = Carbon::yesterday()->toDateString();
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        // 新規ユーザー数
        $newUsers = User::whereBetween('created_at', [$start, $end])->count();

        // 投稿数
        $posts = UserEvent::where('type', 'diary_posted')
            ->whereBetween('occurred_at', [$start, $end])
            ->count();

        // D1 / D7 リテンション
        $d1 = $this->calculateRetention($date, 1);
        $d7 = $this->calculateRetention($date, 7);

        // WAU（過去7日間）
        $wauStart = Carbon::parse($date)->subDays(6)->startOfDay();
        $wau = UserEvent::where('type', 'diary_posted')
            ->whereBetween('occurred_at', [$wauStart, $end])
            ->distinct('user_id')
            ->count('user_id');

        // MAU（過去30日間）
        $mauStart = Carbon::parse($date)->subDays(29)->startOfDay();
        $mau = UserEvent::where('type', 'diary_posted')
            ->whereBetween('occurred_at', [$mauStart, $end])
            ->distinct('user_id')
            ->count('user_id');

        // 週3回以上投稿ユーザー比率
        $weeklyUsers = UserEvent::select('user_id', DB::raw('COUNT(*) as posts'))
            ->where('type', 'diary_posted')
            ->whereBetween('occurred_at', [$wauStart, $end])
            ->groupBy('user_id')
            ->get();

        $totalWeeklyUsers = $weeklyUsers->count();
        $weekly3plusRatio = $totalWeeklyUsers > 0
            ? ($weeklyUsers->where('posts', '>=', 3)->count() / $totalWeeklyUsers) * 100
            : 0;

        // 保存（既存レコードがあれば更新）
        DailyMetric::updateOrCreate(
            ['date' => $date],
            [
                'new_users' => $newUsers,
                'posts' => $posts,
                'd1_retention' => $d1,
                'd7_retention' => $d7,
                'wau' => $wau,
                'mau' => $mau,
                'weekly_3plus_ratio' => $weekly3plusRatio,
            ]
        );

        $this->info("Metrics for {$date} saved.");

        return self::SUCCESS;
    }

    /**
     * Retention計算（登録日の +$days 日に投稿があるか）
     */
    private function calculateRetention(string $date, int $days): float
    {
        $start = Carbon::parse($date)->startOfDay();
        $registeredUsers = User::whereBetween('created_at', [$start, $start->copy()->endOfDay()])
            ->pluck('id');

        if ($registeredUsers->isEmpty()) {
            return 0.0;
        }

        $targetDayStart = Carbon::parse($date)->addDays($days)->startOfDay();
        $targetDayEnd = $targetDayStart->copy()->endOfDay();

        $retainedCount = UserEvent::whereIn('user_id', $registeredUsers)
            ->where('type', 'diary_posted')
            ->whereBetween('occurred_at', [$targetDayStart, $targetDayEnd])
            ->distinct('user_id')
            ->count('user_id');

        return ($retainedCount / $registeredUsers->count()) * 100;
    }
}
