<?php

namespace Tests\Unit\Dto;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Dto\Chart\EmotionPieChartData;

class EmotionPieChartDataTest extends TestCase
{
    /**
     * EmotionPieChartDataのインスタンスが正しく生成されることを確認するテスト
     */
    public function test_emotion_pie_chart_data_shapes()
    {
        $dto = new EmotionPieChartData(['Happy', 'Sad'], [3, 1], ['#fff', '#000']);
        $this->assertCount(2, $dto->labels);
        $this->assertCount(2, $dto->data);
        $this->assertCount(2, $dto->backgroundColor);
        $this->assertEquals(['Happy', 'Sad'], $dto->labels);
        $this->assertEquals([3, 1], $dto->data);
        $this->assertEquals(['#fff', '#000'], $dto->backgroundColor);
    }
}
