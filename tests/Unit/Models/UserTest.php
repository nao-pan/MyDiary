<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_create_user()
    {
        $user = new User();
        $created = $user->createUser([
            'nickname' => 'tester',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $created->id,
            'nickname' => 'tester',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_has_expected_fillable_attributes()
    {
        $user = new User();

        $this->assertEquals([
            'nickname',
            'email',
            'password'
        ], $user->getFillable());
    }

    public function test_user_hide_expected_attributes()
    {
        $user = new User();

        $this->assertEquals([
            'password',
            'remember_token'
        ], $user->getHidden());
    }
    
    public function test_user_casts_email_verified_at_to_datetime()
    {
        $user = new User();

        $this->assertEquals([
            'email_verified_at' => 'datetime',
            'id' => 'int'
        ], $user->getCasts());
    }

    public function test_user_has_many_diaries()
    {
        $user = new User();
        $relation = $user->diaries();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_user_has_many_social_accounts()
    {
        $user = new User();
        $relation = $user->socialAccounts();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_user_has_many_unlocked_emotions()
    {
        $user = new User();
        $relation = $user->unlockedEmotions();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_can_get_user_by_id()
    {
        $user = User::factory()->create();

        $this->assertEquals($user->id, User::find($user->id)->id);
    }

}
