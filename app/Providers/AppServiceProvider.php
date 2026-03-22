<?php

namespace App\Providers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Settings\GeneralSettings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Author::class, UserPolicy::class);
        Gate::policy(Publisher::class, UserPolicy::class);
        Gate::policy(Genre::class, UserPolicy::class);
        Gate::policy(Book::class, UserPolicy::class);
        Gate::policy(Transaction::class, UserPolicy::class);

        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
        // Model::preventAccessingMissingAttributes(! $this->app->isProduction());
        $this->configureDates();
        $this->configureVite();
        $this->shareSettings();
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }

    private function shareSettings(): void
    {
        View::share('settings', $this->resolveSettings());
    }

    private function resolveSettings(): ?GeneralSettings
    {
        try {
            $settingsTable = (new SettingsProperty)->getTable();

            if (! Schema::hasTable($settingsTable)) {
                return null;
            }

            return app(GeneralSettings::class);
        } catch (Throwable) {
            return null;
        }
    }
}
