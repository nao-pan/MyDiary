<?php

namespace Database\Seeders;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class SocialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function ($user) {
            SocialAccount::factory()
                ->for($user)
                ->create();
        });
    }
}
