<?php

namespace Tests\Unit\Dto;

use App\Dto\Chart\MonthlyEmotionBarChartData;
use Tests\TestCase;

class MonthlyEmotionBarChartDataTest extends TestCase
{
    /**
     * MonthlyEmotionBarChartDataのインスタンスが正しく生成されることを確認するテスト
     */
    public function test_monthly_emotion_bar_chart_data_shapes()
    {
        $dto = new MonthlyEmotionBarChartData(
            ['January', 'February'],
            [
                ['label' => 'Happy', 'data' => [10, 20], 'backgroundColor' => '#FF0000'],
                ['label' => 'Sad', 'data' => [5, 15], 'backgroundColor' => '#0000FF'],
            ],
            ['responsive' => true]
        );

        $this->assertCount(2, $dto->labels);
        $this->assertCount(2, $dto->datasets);
        $this->assertEquals(['January', 'February'], $dto->labels);
        $this->assertEquals(['responsive' => true], $dto->options);
    }

    public function test_to_array()
    {
        $dto = new MonthlyEmotionBarChartData(
            ['March', 'April'],
            [
                ['label' => 'Angry', 'data' => [30, 40], 'backgroundColor' => '#00FF00'],
            ]
        );

        $expectedArray = [
            'labels' => ['March', 'April'],
            'datasets' => [
                ['label' => 'Angry', 'data' => [30, 40], 'backgroundColor' => '#00FF00'],
            ],
            'options' => [],
        ];

        $this->assertEquals($expectedArray, $dto->toArray());
    }
}
