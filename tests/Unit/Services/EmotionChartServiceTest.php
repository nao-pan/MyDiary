<?php

namespace Tests\Unit\Services;

use App\Dto\Chart\EmotionPieChartData;
use App\Dto\Chart\MonthlyEmotionBarChartData;
use App\Models\Diary;
use App\Models\EmotionLog;
use App\Models\User;
use App\Services\EmotionChartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

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
        $this->assertEquals(array_sum($result->data), 0);
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

    public function test_get_monthly_chart_data()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->count(10)->create([
            'diary_id' => $diary->id,
        ]);

        $result = $this->emotionChartService->getMonthlyChartData($user, 6);

        $this->assertInstanceOf(MonthlyEmotionBarChartData::class, $result);
        $this->assertCount(6, $result->labels);
        foreach ($result->datasets as $dataset) {
            $this->assertCount(6, $dataset['data']);
        }
    }

    public function test_collect_emotion_counts_by_month_skips_logs_for_months_not_in_labels(): void
    {
        // テストの現在日時を固定（任意）
        Carbon::setTestNow('2025-03-15 12:00:00');

        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);

        // 1月/2月/3月のログを作成。2月は「抜け月」として無視させたい
        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'created_at' => Carbon::parse('2025-01-10'),
            'emotion_state' => 'happy',
        ]);
        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'created_at' => Carbon::parse('2025-02-10'), // ← ここが「continue」で無視される対象
            'emotion_state' => 'happy',
        ]);
        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'created_at' => Carbon::parse('2025-03-05'),
            'emotion_state' => 'happy',
        ]);

        // ラベルは「1月と3月のみ」＝ 2月が存在しない配列にする
        $labels = ['2025-01', '2025-03'];

        // ベース感情の配列（実装に合わせて取得）
        // 例1: BaseEmotion::cases() がある場合
        // $baseEmotions = \App\Enums\BaseEmotion::cases();

        // 例2: まずは最小で1種だけ使う（value はメソッド内で使われる）
        // もし Enum を使っているなら、そのケースを1つ渡してください。
        // 下はダミー例：['happy'] のような「->value を返すオブジェクト」想定。
        // プロジェクトのEnumに合わせて書き換えてください。
        $baseEmotions = [
            (object) ['value' => 'happy'], // 例: BaseEmotion::happy()->value
        ];

        // private メソッドをReflectionで直接呼ぶ
        $ref = new \ReflectionMethod(EmotionChartService::class, 'collectEmotionCountsByMonth');
        $ref->setAccessible(true);

        /** @var array<string,array<string,int>> $result */
        $result = $ref->invoke(
            $this->emotionChartService,
            $user,
            $baseEmotions,
            $labels
        );

        $this->assertSame(1, $result['happy']['2025-01']);
        $this->assertSame(1, $result['happy']['2025-03']);
        $this->assertArrayNotHasKey('2025-02', $result['happy']);

        // Carbon のテスト固定を解除（任意）
        Carbon::setTestNow();
    }
}
