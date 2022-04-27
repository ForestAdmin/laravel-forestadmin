<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use ForestAdmin\LaravelForestAdmin\Listeners\ArtisanStart;
use ForestAdmin\LaravelForestAdmin\Listeners\RouteMatched as RouterMatchedListener;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

/**
 * Class EventProvider
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class EventProvider extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        CommandStarting::class => [
            ArtisanStart::class,
        ],
        RouteMatched::class => [
            RouterMatchedListener::class,
        ],
    ];
}
