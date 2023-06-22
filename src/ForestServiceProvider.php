<?php

namespace ForestAdmin\LaravelForestAdmin;

use ForestAdmin\LaravelForestAdmin\Commands\ForestClear;
use ForestAdmin\LaravelForestAdmin\Commands\ForestInstall;
use ForestAdmin\LaravelForestAdmin\Commands\SendApimap;
use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestCors;
use ForestAdmin\LaravelForestAdmin\Providers\AuthorizationProvider;
use ForestAdmin\LaravelForestAdmin\Providers\EventProvider;
use ForestAdmin\LaravelForestAdmin\Providers\RouteServiceProvider;
use ForestAdmin\LaravelForestAdmin\Services\ChartApiResponse;
use ForestAdmin\LaravelForestAdmin\Services\ForestSchemaInstrospection;
use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

/**
 * Class ForestServiceProvider
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     * @param Kernel $kernel
     * @return void
     */
    public function boot(Kernel $kernel): void
    {
        $this->app->register(EventProvider::class);
        $this->app->register(AuthorizationProvider::class);
        $this->app->register(RouteServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ForestClear::class,
                    ForestInstall::class,
                    SendApimap::class,
                ]
            );
        }

        $this->publishes(
            [
                $this->configFile() => $this->app['path.config'] . DIRECTORY_SEPARATOR . 'forest.php',
            ],
            'config'
        );

        $kernel->pushMiddleware(ForestCors::class);

        $this->app->bind('chart-api', fn() => new ChartApiResponse());
        $this->app->bind('forest-schema', fn() => new ForestSchemaInstrospection());
        $this->app->bind('json-api', fn() => new JsonApiResponse());
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->configFile(), 'forest');

        config(
            [
                'auth.guards.forest' => array_merge(
                    [
                        'driver' => 'forest-token',
                    ],
                    config('auth.guards.forest', [])
                ),
            ]
        );
    }

    /**
     * Get path schema file.
     *
     * @return string
     */
    protected function configFile(): string
    {
        return realpath(__DIR__ . '/../config/forest.php');
    }
}
