<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use ImranDevBd\AiHub\Facades\AIHub;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        AIHub::auth(function ($request) {
            $user = $request->user();

            return app()->environment('local')
                || ($user instanceof User && $user->role === UserRole::SuperAdmin);
        });

        Gate::define('viewAiHub', fn (User $user) => $user->role === UserRole::SuperAdmin);
    }
}
