<?php

namespace App\Services;

use App\Models\UserEvent;

class EventLogger
{
    public function log(?int $userId, string $type, array $meta = []): void
    {
        UserEvent::create([
            'user_id' => $userId,
            'type' => $type,
            'meta' => $meta,
            'occurred_at' => now(),
        ]);
    }
}
