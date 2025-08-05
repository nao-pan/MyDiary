<?php

namespace Tests\Unit;

use App\Enums\EmotionState;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EmotionStateTest extends TestCase
{
    #[test]
    public function all_enum_cases_are_defined()
    {
        $cases = EmotionState::cases();
        $this->assertNotEmpty($cases);
        $this->assertContains(EmotionState::HAPPY, $cases);
    }

    #[test]
    public function label_method_returns_japanese_label()
    {
        $this->assertEquals('嬉しい', EmotionState::HAPPY->label());
        $this->assertEquals('誇らしい', EmotionState::PROUD->label());
    }

    #[test]
    public function base_category_is_correct()
    {
        $this->assertEquals(EmotionState::HAPPY, EmotionState::FUN->baseCategory());
        $this->assertEquals(EmotionState::SAD, EmotionState::MELANCHOLY->baseCategory());
        $this->assertNull(EmotionState::CONFUSED->baseCategory());
    }

    #[test]
    public function default_color_is_returned()
    {
        $this->assertEquals('#4CAF50', EmotionState::HAPPY->defaultColor());
        $this->assertEquals('#FF9800', EmotionState::EXCITED->defaultColor());
    }

    #[test]
    public function unlock_threshold_is_correct()
    {
        $this->assertNull(EmotionState::HAPPY->unlockThreshold());
        $this->assertEquals(10, EmotionState::PROUD->unlockThreshold());
        $this->assertEquals(20, EmotionState::GRATEFUL->unlockThreshold());
    }

    #[test]
    public function is_initially_unlocked_judges_correctly()
    {
        $this->assertTrue(EmotionState::HAPPY->isInitiallyUnlocked());
        $this->assertFalse(EmotionState::ANXIOUS->isInitiallyUnlocked());
    }

    #[test]
    public function unlock_base_emotion_returns_expected_value()
    {
        $this->assertEquals(EmotionState::HAPPY, EmotionState::GRATEFUL->unlockBaseEmotion());
        $this->assertEquals(EmotionState::SAD, EmotionState::MELANCHOLY->unlockBaseEmotion());
        $this->assertNull(EmotionState::CONFUSED->unlockBaseEmotion());
    }

    #[test]
    public function values_method_returns_all_enum_values()
    {
        $values = EmotionState::values();
        $this->assertContains('happy', $values);
        $this->assertContains('confused', $values);
    }

    #[test]
    public function base_emotions_returns_expected_array()
    {
        $base = EmotionState::baseEmotions();
        $this->assertContains(EmotionState::HAPPY, $base);
        $this->assertContains(EmotionState::DISGUSTED, $base);
        $this->assertNotContains(EmotionState::CONFUSED, $base);
    }

    #[test]
    public function from_label_returns_enum_instance()
    {
        $this->assertEquals(EmotionState::HAPPY, EmotionState::fromLabel('嬉しい'));
        $this->assertEquals(EmotionState::MELANCHOLY, EmotionState::fromLabel('憂鬱'));
        $this->assertNull(EmotionState::fromLabel('存在しない'));
    }

    #[test]
    public function text_color_is_returned_correctly()
    {
        $this->assertEquals('#000000', EmotionState::HAPPY->textColor());
        $this->assertEquals('#FFFFFF', EmotionState::SAD->textColor());
    }
}
