<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Diary;
use App\Policies\DiaryPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Diary::class => DiaryPolicy::class,
    ];
}
