<?php

namespace Tests\Unit\Dto;

use App\Dto\EmotionStatus;
use Tests\TestCase;

class EmotionStatusTest extends TestCase
{
    public function test_to_array()
    {
        $emotionStatus = new EmotionStatus(
            key: 'happy',
            label: 'Happy',
            defaultColor: '#FFFF00',
            textColor: '#000000',
            unlocked: true,
            threshold: 5,
            unlockType: 'post_count',
            currentCount: 10,
            remaining: null,
            baseEmotion: null,
            isInitial: false
        );

        $expectedArray = [
            'key' => 'happy',
            'label' => 'Happy',
            'color' => '#FFFF00',
            'textColor' => '#000000',
            'unlocked' => true,
            'threshold' => 5,
            'unlockType' => 'post_count',
            'currentCount' => 10,
            'remaining' => null,
            'baseEmotion' => null,
            'isInitial' => false,
        ];

        $this->assertEquals($expectedArray, $emotionStatus->toArray());
    }

    public function test_json_serialize()
    {
        $emotionStatus = new EmotionStatus(
            key: 'sad',
            label: 'Sad',
            defaultColor: '#0000FF',
            textColor: '#FFFFFF',
            unlocked: false,
            threshold: 3,
            unlockType: 'initial',
            currentCount: 1,
            remaining: 2,
            baseEmotion: 'neutral',
            isInitial: true
        );

        $expectedArray = $emotionStatus->toArray();

        $this->assertEquals($expectedArray, $emotionStatus->jsonSerialize());
    }
}
