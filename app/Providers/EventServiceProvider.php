<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Native\Laravel\Events\MenuItemClicked;
use App\Listeners\StartSshConnection;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, class-string|string>
     */
    protected $listen = [
        MenuItemClicked::class => [
            StartSshConnection::class,
        ],
    ];
}