<?php

namespace Tests\Unit\Policies;

use App\Models\Diary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DiaryPolicyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_diary_policy_view_owner_allows()
    {
        [$owner, $other] = [User::factory()->create(), User::factory()->create()];
        $diary = Diary::factory()->for($owner)->create();

        $this->assertTrue(Gate::forUser($owner)->allows('view', $diary));
        $this->assertFalse(Gate::forUser($other)->allows('view', $diary));
    }

    public function test_diary_policy_update_owner_allows()
    {
        [$owner, $other] = [User::factory()->create(), User::factory()->create()];
        $diary = Diary::factory()->for($owner)->create();

        $this->assertTrue(Gate::forUser($owner)->allows('update', $diary));
        $this->assertFalse(Gate::forUser($other)->allows('update', $diary));
    }
}
