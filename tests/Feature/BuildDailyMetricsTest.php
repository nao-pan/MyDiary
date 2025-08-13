<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserEvent;
use App\Models\DailyMetric;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuildDailyMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function runMetricsForYesterday(): DailyMetric
    {
        $yesterday = now()->subDay()->toDateString();
        $this->artisan('metrics:build-daily')->assertExitCode(0);
        return DailyMetric::where('date', $yesterday)->first();
    }

    public function test_posts_count_only_yesterday(): void
    {
        Carbon::setTestNow('2025-08-12');
        $yesterday = now()->subDay()->toDateString();

        $user = User::factory()->create(['created_at' => $yesterday]);

        // 昨日投稿
        UserEvent::create([
            'user_id' => $user->id,
            'type' => 'diary_posted',
            'occurred_at' => Carbon::parse($yesterday)->setTime(10, 0),
        ]);

        $metric = $this->runMetricsForYesterday();

        $this->assertEquals(1, $metric->posts);
    }

    public function test_d1_and_d7_retention(): void
    {
        Carbon::setTestNow('2025-08-12');
        $yesterday = now()->subDay()->toDateString();

        $u1 = User::factory()->create(['created_at' => $yesterday]);
        $u2 = User::factory()->create(['created_at' => $yesterday]);

        // D1投稿（今日）
        UserEvent::create([
            'user_id' => $u1->id,
            'type' => 'diary_posted',
            'occurred_at' => now()->setTime(10, 0),
        ]);

        // D7投稿なし（D7=0%）
        $metric = $this->runMetricsForYesterday();

        $this->assertEquals(50.0, $metric->d1_retention);
        $this->assertEquals(0.0, $metric->d7_retention);
    }

    public function test_weekly_3plus_ratio_100_percent(): void
    {
        Carbon::setTestNow('2025-08-12');

        $user = User::factory()->create(['created_at' => now()->subDays(5)]);

        // 「昨日～3日前」の3回投稿（= 集計ウィンドウ [昨日-6日, 昨日] に完全に入る）
        foreach ([1, 2, 3] as $d) {
            UserEvent::create([
                'user_id'    => $user->id,
                'type'       => 'diary_posted',
                'meta'       => ['emotionstate' => 'HAPPY', 'diary_id' => 100 + $d],
                'occurred_at' => now()->subDays($d)->setTime(12, 0),
            ]);
        }
        $metric = $this->runMetricsForYesterday();

        $this->assertEquals(100.0, $metric->weekly_3plus_ratio);
    }

    public function test_weekly_3plus_ratio_0_percent(): void
    {
        Carbon::setTestNow('2025-08-12');
        $yesterday = now()->subDay()->toDateString();

        $user = User::factory()->create(['created_at' => now()->subDays(5)]);

        // 週内に1回投稿
        UserEvent::create([
            'user_id' => $user->id,
            'type' => 'diary_posted',
            'occurred_at' => now()->subDay()->setTime(12, 0),
        ]);

        $metric = $this->runMetricsForYesterday();

        $this->assertEquals(0.0, $metric->weekly_3plus_ratio);
    }
}
