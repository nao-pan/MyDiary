<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Dto\Chart\EmotionPieChartData;
use App\Dto\Chart\MonthlyEmotionBarChartData;
use App\Models\EmotionLog;
use App\Models\User;
use App\Enums\EmotionState;
use App\Models\EmotionColor;
use App\Models\Diary;
use App\Services\EmotionChartService;

class EmotionChartServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $emotionChartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emotionChartService = app(EmotionChartService::class);
    }

    /**
     * ユーザーの投稿が０件の場合、グラフデータが0であることを確認するテスト
     */
    public function test_emotion_chart_data_is_empty_when_user_has_not_post()
    {
        $user = User::factory()->create();
        $result = $this->emotionChartService->getEmotionPieChartData($user);

        $this->assertInstanceOf(EmotionPieChartData::class, $result);
        $this->assertEquals(array_sum($result->data), 0) ;
    }

    public function test_get_emotion_chart_data()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);
        $logs = EmotionLog::factory()->count(5)->happy()->create(['diary_id' => $diary->id]);

        $result = $this->emotionChartService->getEmotionPieChartData($user);

        $this->assertInstanceOf(EmotionPieChartData::class, $result);
        $this->assertCount(6, $result->labels);
        $this->assertEquals(array_sum($result->data), 5);
    }

    public function testGetMonthlyChartData()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->count(10)->create([
            'diary_id' => $diary->id]);

        $result = $this->emotionChartService->getMonthlyChartData($user, 6);

        $this->assertInstanceOf(MonthlyEmotionBarChartData::class, $result);
        $this->assertCount(6, $result->labels);
        foreach ($result->datasets as $dataset) {
            $this->assertCount(6, $dataset['data']);
        }
    }
}
