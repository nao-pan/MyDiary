<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserEvent;
use App\Services\EventLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_logged_correctly(): void
    {
        $user = User::factory()->create();

        $logger = app(EventLogger::class);
        $logger->log($user->id, 'diary_posted', [
            'emotionstate' => 'HAPPY',
            'diary_id' => 1,
        ]);

        $this->assertDatabaseHas('user_events', [
            'user_id' => $user->id,
            'type' => 'diary_posted',
        ]);

        $event = UserEvent::first();
        $this->assertEquals('HAPPY', $event->meta['emotionstate']);
        $this->assertEquals(1, $event->meta['diary_id']);
        $this->assertNotNull($event->occurred_at);
    }

    public function test_event_can_be_logged_without_user(): void
    {
        $logger = app(EventLogger::class);
        $logger->log(null, 'registered', ['source' => 'invite']);

        $this->assertDatabaseHas('user_events', [
            'type' => 'registered',
        ]);

        $event = UserEvent::first();
        $this->assertEquals('invite', $event->meta['source']);
    }
}
