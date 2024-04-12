<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Roster\RosterParserServiceInterface;
use App\Services\Roster\HtmlRosterParserService;
use App\Services\Event\Interfaces\EventServiceInterface;
use App\Services\Event\EventService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RosterParserServiceInterface::class, HtmlRosterParserService::class);
        $this->app->bind(EventServiceInterface::class, EventService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
