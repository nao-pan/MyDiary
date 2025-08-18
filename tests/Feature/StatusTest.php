<?php

namespace Tests\Feature;

use App\Dto\Chart\EmotionPieChartData;
use App\Dto\Chart\MonthlyEmotionBarChartData;
use App\Dto\EmotionStatus;
use App\Models\User;
use App\Services\EmotionChartService;
use App\Services\EmotionStatusService;
use Mockery;
use Tests\TestCase;

class StatusTest extends TestCase
{
    public function test_status_index_renders_with_view_data()
    {
        $user = User::factory()->create();
        $this->be($user, 'web');

        // サービスを軽くモックして高速化
        $statusMock = Mockery::mock(EmotionStatusService::class);
        $statusMock->shouldReceive('buildEmotionStatuses')->once()->andReturn(collect([
            new EmotionStatus('happy', 'Happy', '#fff', '#000', true, null, 'initial', 0, null, null, true),
        ]));
        $statusMock->shouldReceive('getRecentEmotionScores')->once()->andReturn([1, 0, 0, 0, 0, 0]);
        $this->app->instance(EmotionStatusService::class, $statusMock);

        $chartMock = Mockery::mock(EmotionChartService::class);
        $chartMock->shouldReceive('getEmotionPieChartData')->once()->andReturn(
            new EmotionPieChartData(['Happy'], [1], ['#fff'])
        );
        $chartMock->shouldReceive('getMonthlyChartData')->once()->andReturn(
            new MonthlyEmotionBarChartData(['2025-08'], [['label' => 'Happy', 'data' => [1], 'backgroundColor' => '#fff']], [])
        );
        $this->app->instance(EmotionChartService::class, $chartMock);

        $res = $this->get(route('status.index'));

        $res->assertOk()
            ->assertViewIs('status.index')
            ->assertViewHasAll(['postCount', 'emotionStatuses', 'pieChartData', 'barChartData', 'recentEmotionScores'])
            ->assertViewHas('postCount', 0);
    }
}
