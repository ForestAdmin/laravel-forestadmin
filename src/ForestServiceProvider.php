<?php

namespace ForestAdmin\LaravelForestAdmin;

use ForestAdmin\LaravelForestAdmin\Commands\ForestInstall;
use ForestAdmin\LaravelForestAdmin\Commands\SendApimap;
use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestCors;
use ForestAdmin\LaravelForestAdmin\Providers\AgentProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class ForestServiceProvider extends ServiceProvider
{
    public function boot(Kernel $kernel): void
    {
        $this->app->register(AgentProvider::class);

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
                $this->configFile() => config_path('forest.php'),
            ],
            'config'
        );

        $this->publishes(
            [
                $this->agentTemplateFile() => $this->appForestPath() . DIRECTORY_SEPARATOR . 'forest_admin.php',
            ],
            'forest'
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
        return realpath(__DIR__ . '/../config/forest.php');
    }

    protected function agentTemplateFile(): string
    {
        return realpath(__DIR__ . '/../agentTemplate/forest_admin.php');
    }

    protected function appForestPath(): string
    {
        return base_path() . DIRECTORY_SEPARATOR . 'forest';
    }
}
