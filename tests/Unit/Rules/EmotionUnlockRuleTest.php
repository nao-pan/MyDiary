<?php

namespace Tests\Unit\Rules;

use App\Enums\EmotionState;
use App\Rules\EmotionUnlockRule;
use Tests\TestCase;

class EmotionUnlockRuleTest extends TestCase
{
    public function test_rule_sets_properties_correctly()
    {
        $rule = new EmotionUnlockRule(
            emotion: EmotionState::HAPPY,
            unlockType: 'post_count',
            threshold: 3,
            baseEmotion: EmotionState::SAD,
            conditions: [['type' => 'combo']],
            isSecret: true,
            hint: '投稿してみよう'
        );

        $this->assertEquals(EmotionState::HAPPY, $rule->emotion);
        $this->assertEquals('post_count', $rule->unlockType);
        $this->assertEquals(3, $rule->threshold);
        $this->assertEquals(EmotionState::SAD, $rule->baseEmotion);
        $this->assertEquals([['type' => 'combo']], $rule->conditions);
        $this->assertTrue($rule->isSecret);
        $this->assertEquals('投稿してみよう', $rule->hint);
    }

    public function test_rule_detects_unlock_type_correctly()
    {
        $combo = new EmotionUnlockRule(EmotionState::HAPPY, 'combo');
        $initial = new EmotionUnlockRule(EmotionState::HAPPY, 'initial');
        $postCount = new EmotionUnlockRule(EmotionState::HAPPY, 'post_count');
        $baseEmotion = new EmotionUnlockRule(EmotionState::HAPPY, 'base_emotion');

        $this->assertTrue($combo->isCombo());
        $this->assertFalse($combo->isInitial());

        $this->assertTrue($initial->isInitial());
        $this->assertTrue($postCount->isPostCount());
        $this->assertTrue($baseEmotion->isBaseEmotion());
    }

    public function test_rule_converts_to_array_correctly()
    {
        $rule = new EmotionUnlockRule(
            emotion: EmotionState::HAPPY,
            unlockType: 'base_emotion',
            threshold: 2,
            baseEmotion: EmotionState::FUN,
            conditions: [['type' => 'base_emotion', 'threshold' => 2]],
            isSecret: false,
            hint: '2回笑おう'
        );

        $expected = [
            'emotion' => 'happy',
            'unlockType' => 'base_emotion',
            'threshold' => 2,
            'baseEmotion' => 'fun',
            'conditions' => [['type' => 'base_emotion', 'threshold' => 2]],
            'isSecret' => false,
            'hint' => '2回笑おう',
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    public function test_rule_handles_null_base_emotion_in_to_array()
    {
        $rule = new EmotionUnlockRule(
            emotion: EmotionState::SAD,
            unlockType: 'post_count',
            threshold: 1,
            baseEmotion: null,
            conditions: [],
            isSecret: false,
            hint: null
        );

        $array = $rule->toArray();

        $this->assertNull($array['baseEmotion']);
        $this->assertNull($array['hint']);
    }
}
