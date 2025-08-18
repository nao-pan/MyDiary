<?php

namespace Tests\Unit\Models;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SocialAccountTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_create_social_account()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => '123456789',
            'avatar' => 'http://example.com/avatar.jpg',
            'email' => 'user@example.com',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'id' => $socialAccount->id,
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => '123456789',
        ]);
    }

    public function test_social_account_has_expected_fillable_attributes()
    {
        $socialAccount = new SocialAccount;

        $this->assertEquals([
            'user_id',
            'provider_name',
            'provider_id',
            'avatar',
            'email',
        ], $socialAccount->getFillable());
    }

    public function test_social_account_belongs_to_user()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->create(['user_id' => $user->id]);
        $relation = $socialAccount->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals($user->id, $socialAccount->user_id);
    }
}
