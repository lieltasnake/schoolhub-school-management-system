<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        try {
            Role::ensureDefaults();
        } catch (\Throwable $e) {
            // Ignore during bootstrap if the database is not ready yet.
        }
    }
}
