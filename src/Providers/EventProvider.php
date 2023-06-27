<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use ForestAdmin\LaravelForestAdmin\Listeners\ArtisanStart;
use ForestAdmin\LaravelForestAdmin\Listeners\RouteMatched as RouterMatchedListener;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class EventProvider extends EventServiceProvider
{
    protected $listen = [
        CommandStarting::class => [
            ArtisanStart::class,
        ],
        RouteMatched::class => [
            RouterMatchedListener::class,
        ],
    ];
}
