<?php

namespace App\Providers;

use App\Models\Poll;
use App\Policies\PollPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Poll::class, PollPolicy::class);
    }
}
