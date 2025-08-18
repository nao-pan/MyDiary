<?php

namespace App\Providers;

use App\Models\Diary;
use App\Policies\DiaryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Diary::class => DiaryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // （任意）管理者は常に許可
        // Gate::before(function ($user, $ability) {
        //     return $user->is_admin ? true : null;
        // });
    }
}
