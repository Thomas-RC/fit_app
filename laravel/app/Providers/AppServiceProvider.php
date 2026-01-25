<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Filesystem in local environment to suppress chmod errors (WSL2 + NTFS)
        if ($this->app->environment('local')) {
            $this->app->singleton('files', function ($app) {
                return new class extends Filesystem {
                    public function chmod($path, $mode = null)
                    {
                        // Suppress chmod errors on NTFS in WSL2
                        return @chmod($path, $mode ?? 0755) !== false;
                    }
                };
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set Carbon locale to Polish
        \Carbon\Carbon::setLocale('pl');
    }
}
