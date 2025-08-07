<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\EmotionStatusService;
use Tests\TestCase;

class EmotionStatusServiceTest extends TestCase
{
    use RefreshDatabase;
    protected EmotionStatusService $emotionStatusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emotionStatusService = new EmotionStatusService();
    }

}
