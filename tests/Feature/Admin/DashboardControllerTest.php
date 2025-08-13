<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\DailyMetric;
use App\Models\UserEvent;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function normalUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    /**
     * ルートヘルパ。ルート名が存在しない場合でもURL直指定にフォールバック
     */
    private function adminDashboardUrl(): string
    {
        try {
            return route('admin.dashboard');
        } catch (\Throwable $e) {
            return '/admin/dashboard';
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->adminDashboardUrl())
            ->assertStatus(302) // login へ
            ->assertRedirect();
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get($this->adminDashboardUrl())
            ->assertStatus(403);
    }

    public function test_admin_can_see_dashboard_with_default_period_7(): void
    {
        Carbon::setTestNow('2025-08-12');

        // 過去10日のメトリクスを生成（昨日まで）
        for ($i = 1; $i <= 10; $i++) {
            DailyMetric::factory()->create([
                'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $admin = $this->adminUser();

        $res = $this->actingAs($admin)
            ->get($this->adminDashboardUrl().'?period=7')
            ->assertStatus(200)
            ->assertViewIs('admin.dashboard')
            ->assertViewHasAll(['metrics', 'emotionDistribution', 'period']);

        // metrics が 7件（過去7日）になっていること
        $metrics = $res->viewData('metrics');
        $this->assertCount(7, $metrics);
    }

    public function test_period_30_returns_up_to_30_days(): void
    {
        Carbon::setTestNow('2025-08-12');

        // 過去15日分を用意（昨日まで）
        for ($i = 1; $i <= 15; $i++) {
            DailyMetric::factory()->create([
                'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $admin = $this->adminUser();

        $res = $this->actingAs($admin)
            ->get($this->adminDashboardUrl().'?period=30')
            ->assertStatus(200)
            ->assertViewHas('metrics');

        $metrics = $res->viewData('metrics');
        $this->assertCount(15, $metrics); // 用意した分だけ返る
    }

    public function test_period_all_returns_all_with_internal_cap(): void
    {
        Carbon::setTestNow('2025-08-12');

        // 過去40日ぶん
        for ($i = 1; $i <= 40; $i++) {
            DailyMetric::factory()->create([
                'date' => now()->subDays($i)->toDateString(),
            ]);
        }

        $admin = $this->adminUser();

        $res = $this->actingAs($admin)
            ->get($this->adminDashboardUrl().'?period=all')
            ->assertStatus(200)
            ->assertViewHas('metrics');

        $metrics = $res->viewData('metrics');
        // コントローラ実装：take(365) のはずなので 40件返る
        $this->assertCount(40, $metrics);
    }

    public function test_emotion_distribution_is_aggregated_within_period_window(): void
    {
        Carbon::setTestNow('2025-08-12');

        $admin = $this->adminUser();

        // 期間 = デフォルト7日（昨日～6日前）
        $yesterday = now()->subDay()->setTime(12, 0);
        $threeDaysAgo = now()->subDays(3)->setTime(12, 0);
        $fortyDaysAgo = now()->subDays(40)->setTime(12, 0);

        // 期間内（カウント対象）
        $u = User::factory()->create();
        UserEvent::create([
            'user_id' => $u->id,
            'type' => 'diary_posted',
            'meta' => ['emotionstate' => 'HAPPY', 'diary_id' => 1],
            'occurred_at' => $yesterday,
        ]);
        UserEvent::create([
            'user_id' => $u->id,
            'type' => 'diary_posted',
            'meta' => ['emotionstate' => 'HAPPY', 'diary_id' => 2],
            'occurred_at' => $threeDaysAgo,
        ]);
        UserEvent::create([
            'user_id' => $u->id,
            'type' => 'diary_posted',
            'meta' => ['emotionstate' => 'SAD', 'diary_id' => 3],
            'occurred_at' => $threeDaysAgo,
        ]);

        // 期間外（カウント対象外）
        UserEvent::create([
            'user_id' => $u->id,
            'type' => 'diary_posted',
            'meta' => ['emotionstate' => 'JOY', 'diary_id' => 4],
            'occurred_at' => $fortyDaysAgo,
        ]);

        $res = $this->actingAs($admin)->get($this->adminDashboardUrl())
            ->assertStatus(200)
            ->assertViewHasAll(['emotionDistribution', 'period']);

        $dist = $res->viewData('emotionDistribution');

        // 期間内の集計が正しいこと（HAPPY=2, SAD=1）
        $this->assertEquals(2, $dist['HAPPY'] ?? null);
        $this->assertEquals(1, $dist['SAD'] ?? null);
        // 期間外のJOYは含まれない
        $this->assertArrayNotHasKey('JOY', $dist);
    }
}
