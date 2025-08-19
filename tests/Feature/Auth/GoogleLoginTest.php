<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use App\Models\User;
use App\Models\SocialAccount;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{

    use RefreshDatabase;

    protected function mockGoogleUser(array $overrides = [])
    {
        $googleUser = new SocialiteUser();
        $googleUser->id = $overrides['id'] ?? 'google-id-123';
        $googleUser->name = $overrides['name'] ?? 'Test User';
        $googleUser->email = $overrides['email'] ?? 'test@example.com';
        $googleUser->avatar = $overrides['avatar'] ?? 'http://avatar.url';

        Socialite::shouldReceive('driver->user')
            ->andReturn($googleUser);

        return $googleUser;
    }

    public function test_redirect_to_google()
    {
        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect(); // GoogleのOAuth画面へリダイレクト
    }

    public function test_callback_with_existing_social_account_logs_in_user()
    {
        $user = User::factory()->create();
        $linked = SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => 'google-id-123',
        ]);

        $this->mockGoogleUser(['id' => 'google-id-123', 'email' => $user->email]);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('diary.index'));
    }

    public function test_callback_with_existing_email_links_account()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->mockGoogleUser(['id' => 'new-google-id', 'email' => $user->email]);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => 'new-google-id',
        ]);
        $response->assertRedirect(route('diary.index'));
    }

    public function test_callback_creates_new_user_if_no_match()
    {
        $this->mockGoogleUser([
            'id' => 'unique-google-id',
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);

        $response = $this->get(route('auth.google.callback'));

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => 'unique-google-id',
        ]);
        $response->assertRedirect(route('diary.index'));
    }

    public function test_callback_creates_new_user_when_email_is_null()
    {
        // Socialite のモック
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('null-email-id');
        $abstractUser->shouldReceive('getEmail')->andReturn(null); // null を返す
        $abstractUser->shouldReceive('getName')->andReturn('NoEmailUser');
        $abstractUser->shouldReceive('getAvatar')->andReturn('http://example.com/avatar.png');

        Socialite::shouldReceive('driver->user')->andReturn($abstractUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('diary.index'));

        // DBに新規ユーザーが作成されていることを確認
        $this->assertDatabaseHas('users', [
            'nickname' => 'NoEmailUser',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider_name' => 'google',
            'provider_id'   => 'null-email-id',
        ]);
    }
}
