<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootTest extends TestCase
{

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_root_redirects_logged_in_user_to_diary_index(): void
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect(route('diary.index'));
    }
}
