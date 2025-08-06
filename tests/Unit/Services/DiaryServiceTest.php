<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\DiaryService;
use App\Models\User;
use App\Enums\EmotionState;
use App\Models\Diary;
use App\Models\EmotionLog;
use App\Models\EmotionColor;
use App\Services\EmotionLogService;
use Illuminate\Database\QueryException;
use Mockery\Exception\RuntimeException;
use Tests\TestCase;
use Mockery;

class DiaryServiceTest extends TestCase
{
  use RefreshDatabase;

  protected $diaryService;
  protected $mockEmotionLogService;

  protected function setUp(): void
  {
    parent::setUp();
    // EmotionLogService をモック
    $this->mockEmotionLogService = new EmotionLogService();
    $this->diaryService = new DiaryService($this->mockEmotionLogService);
  }

  /**
   * 日記と感情ログの作成テスト
   */
  public function test_create_with_emotion()
  {
    $user = User::factory()->create();
    $data = [
      'title' => 'Test Diary',
      'content' => 'This is a test diary entry.',
      'emotion_state' => EmotionState::HAPPY->value,
      'emotion_score' => 0.5,
      'happiness_score' => 8,
    ];

    $diary = $this->diaryService->createWithEmotion($user, $data);

    $this->assertDatabaseHas('diaries', [
      'id' => $diary->id,
      'user_id' => $user->id,
      'title' => 'Test Diary',
      'content' => 'This is a test diary entry.',
      'happiness_score' => 8,
    ]);

    $this->assertDatabaseHas('emotion_logs', [
      'diary_id' => $diary->id,
      'emotion_state' => EmotionState::HAPPY->value,
      'emotion_score' => 0.5,
    ]);
    $this->assertInstanceOf(Diary::class, $diary);
  }

  /**
   * happiness_scoreがnullの場合でも保存可能であるテスト
   */
  public function test_create_with_null_happiness_score()
  {
    $user = User::factory()->create();
    $data = [
      'title' => 'Test Diary with Null Happiness Score',
      'content' => 'This diary has no happiness score.',
      'emotion_state' => EmotionState::SAD->value,
      'emotion_score' => 0.3,
    ];

    $diary = $this->diaryService->createWithEmotion($user, $data);
    $this->assertDatabaseHas('diaries', [
      'id' => $diary->id,
      'user_id' => $user->id,
      'title' => 'Test Diary with Null Happiness Score',
      'content' => 'This diary has no happiness score.',
      'happiness_score' => null,
    ]);
    $this->assertDatabaseHas('emotion_logs', [
      'diary_id' => $diary->id,
      'emotion_state' => EmotionState::SAD->value,
      'emotion_score' => 0.3,
    ]);
    $this->assertInstanceOf(Diary::class, $diary);
  }

  /**
   * 不正データでの作成が失敗することを確認するテスト
   * タイトルがないケース
   */
  public function test_create_with_invalid_data()
  {
    $this->expectException(QueryException::class);

    $user = User::factory()->create();
    $data = [
      'title' => null, // タイトルが空
      'content' => 'Diary with Invalid Data',
      'emotion_state' => EmotionState::SAD->value,
      'emotion_score' => 0.3,
    ];

    $this->diaryService->createWithEmotion($user, $data);
  }

  /**
   * EmotionLog保存失敗時にトランザクションがロールバックされることを確認するテスト
   */
  public function test_create_with_emotion_log_failure_should_rollback()
  {
    $user = User::factory()->create();
    $data = [
      'title' => 'Test Diary',
      'content' => 'This is a test diary entry.',
      'emotion_state' => EmotionState::HAPPY->value,
      'emotion_score' => 0.5,
    ];

    $mock = Mockery::mock(EmotionLogService::class);
    $mock->shouldReceive('create')
      ->once()
      ->andThrow(new \RuntimeException('Simulated failure'));

    $diaryService = new DiaryService($mock);

    // トランザクションがロールバックされていることを確認
    try {
      $diaryService->createWithEmotion($user, $data);
      $this->fail('例外が発生しませんでした');
    } catch (\RuntimeException $e) {
      $this->assertDatabaseMissing('diaries', [
        'user_id' => $user->id,
        'title' => 'Test Diary',
        'content' => 'This is a test diary entry.',
        'happiness_score' => null,
      ]);
    }
  }

  public function test_get_calendar_events_for_user()
  {
    $user = User::factory()->create();
    $diary = Diary::factory()->create(['user_id' => $user->id]);
    $emotionLog = EmotionLog::factory()->create([
      'diary_id' => $diary->id,
      'emotion_state' => EmotionState::HAPPY->value,
      'emotion_score' => 0.8,
      'created_at' => now(),
    ]);

    $events = $this->diaryService->getCalendarEventsForUser($user);

    $this->assertCount(1, $events);
    $this->assertEquals($emotionLog->created_at->format('Y-m-d'), $events[0]['start']);
    $this->assertEquals(EmotionState::HAPPY->defaultColor(), $events[0]['color']);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }
}
