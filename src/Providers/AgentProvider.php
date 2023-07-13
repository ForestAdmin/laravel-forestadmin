<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Http\Router as AgentRouter;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\ForestController;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\ServiceProvider;

class AgentProvider extends ServiceProvider
{
    protected RouteCollection $routes;

    public function boot()
    {
        if ($this->forestIsPlanted()) {
            $this->app->instance(AgentFactory::class, new AgentFactory($this->loadOptions()));
            $this->loadConfiguration();
        }
    }

    /**
     * @return RouteCollection
     */
    public function loadRoutes()
    {
        $prefix = '/forest';

        foreach (AgentRouter::getRoutes() as $name => $agentRoute) {
            $this->app['router']->addRoute($this->transformMethodsValuesToUpper($agentRoute['methods']), $prefix . $agentRoute['uri'], [ForestController::class, '__invoke'])->name($name);
        }
    }

    private function forestIsPlanted(): bool
    {
        return env('FOREST_AUTH_SECRET') && env('FOREST_ENV_SECRET');
    }

    private function transformMethodsValuesToUpper(array|string $methods): array
    {
        if (is_string($methods)) {
            return [strtoupper($methods)];
        }

        return array_map(fn ($method) => strtoupper($method), $methods);
    }

    private function loadConfiguration(): void
    {
        if (file_exists($this->app['path.config'] . DIRECTORY_SEPARATOR . 'forest_admin.php')) {
            $callback = require $this->app['path.config'] . DIRECTORY_SEPARATOR . 'forest_admin.php';
            $callback($this);

            $this->app->make(AgentFactory::class)->build();
            $this->loadRoutes();
        }
    }

    private function loadOptions(): array
    {
        return [
            'debug'                => env('FOREST_DEBUG', true),
            'authSecret'           => env('FOREST_AUTH_SECRET'),
            'envSecret'            => env('FOREST_ENV_SECRET'),
            'forestServerUrl'      => env('FOREST_SERVER_URL', 'https://api.forestadmin.com'),
            'isProduction'         => env('FOREST_ENVIRONMENT', 'dev') === 'prod',
            'prefix'               => env('FOREST_PREFIX', 'forest'),
            'permissionExpiration' => env('FOREST_PERMISSIONS_EXPIRATION_IN_SECONDS', 300),
            'cacheDir'             => storage_path('framework/cache/data/forest'),
            'schemaPath'           => base_path() . '/.forestadmin-schema.json',
            'projectDir'           => base_path(),
        ];
    }
}
