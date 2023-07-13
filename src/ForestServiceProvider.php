<?php

namespace ForestAdmin\LaravelForestAdmin;

use ForestAdmin\LaravelForestAdmin\Commands\ForestInstall;
use ForestAdmin\LaravelForestAdmin\Commands\SendApimap;
use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestCors;
use ForestAdmin\LaravelForestAdmin\Providers\AgentProvider;
use ForestAdmin\LaravelForestAdmin\Providers\EventProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class ForestServiceProvider extends ServiceProvider
{
    public function boot(Kernel $kernel): void
    {
        $this->app->register(AgentProvider::class);
        $this->app->register(EventProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    ForestInstall::class,
                    SendApimap::class,
                ]
            );
        }

        $this->publishes(
            [
                $this->configFile() => $this->app['path.config'] . DIRECTORY_SEPARATOR . 'forest_admin.php',
            ],
            'config'
        );

        $kernel->pushMiddleware(ForestCors::class);
    }

    /**
     * Get path schema file.
     *
     * @return string
     */
    protected function configFile(): string
    {
        return realpath(__DIR__ . '/../config/forest_admin.php');
    }
}
