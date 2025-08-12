<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use App\Rules\UnlockRuleRepository;
use App\Enums\EmotionState;
use Illuminate\Support\Facades\File;

class UnlockRuleRepositoryTest extends TestCase
{
  /**
   * UnlockRuleRepository::all() が正しくルールを読み込むことをテスト
   */
  public function test_it_loads_all_rules()
  {
    File::shouldReceive('get')
      ->once()
      ->with(resource_path('data/emotion_unlock_rules.json'))
      ->andReturn(json_encode([
        [
          'emotion' => 'grateful',
          'unlockType' => 'post_count',
          'threshold' => 3,
          'isSecret' => false,
          'hint' => '3回投稿しよう'
        ],
      ]));

    $repo = new UnlockRuleRepository();

    $rules = $repo->all();

    $this->assertCount(1, $rules);
    $this->assertEquals(EmotionState::GRATEFUL, $rules[0]->emotion);
    $this->assertEquals('post_count', $rules[0]->unlockType);
    $this->assertEquals(3, $rules[0]->threshold);
    $this->assertFalse($rules[0]->isSecret);
    $this->assertEquals('3回投稿しよう', $rules[0]->hint);
  }

  /**
   * UnlockRuleRepository::getByEmotion() が特定感情にマッチするルールを返すことをテスト
   */
  public function test_it_returns_rule_by_emotion()
  {
    File::shouldReceive('get')
      ->once()
      ->andReturn(json_encode([
        [
          'emotion' => 'grateful',
          'unlockType' => 'post_count',
          'threshold' => 5
        ],
        [
          'emotion' => 'anxious',
          'unlockType' => 'initial'
        ]
      ]));

    $repo = new UnlockRuleRepository();

    $rule = $repo->getByEmotion(EmotionState::GRATEFUL);

    $this->assertNotNull($rule);
    $this->assertEquals('post_count', $rule->unlockType);
    $this->assertEquals(5, $rule->threshold);
  }

  public function test_it_throws_exception_when_invalid_emotion_state_provided()
  {
    File::shouldReceive('get')
      ->once()
      ->andReturn(json_encode([
        ['emotion' => 'not_existing_emotion', 'unlockType' => 'initial']
      ]));

    $this->expectException(\ValueError::class); // Enum::from() に無効値を渡した場合の例外

    new UnlockRuleRepository();
  }

  public function test_it_throws_exception_when_json_is_invalid()
  {
    File::shouldReceive('get')
      ->once()
      ->andReturn('{invalid json');

    $this->expectException(\RuntimeException::class); // 実際のjson_decodeの失敗時
    $this->expectExceptionMessage('Invalid JSON in emotion_unlock_rules.json');

    new UnlockRuleRepository();
  }

  public function test_it_returns_null_when_emotion_does_not_match()
  {
    File::shouldReceive('get')
      ->once()
      ->andReturn(json_encode([
        ['emotion' => 'happy', 'unlockType' => 'initial']
      ]));

    $repo = new UnlockRuleRepository();

    $this->assertNull($repo->getByEmotion(EmotionState::SAD)); // 存在しない感情を指定
  }
}
