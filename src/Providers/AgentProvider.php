<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Http\Router as AgentRouter;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\ServiceProvider;

class AgentProvider extends ServiceProvider
{
    protected RouteCollection $routes;

    public function boot()
    {
        $this->app->bind(AgentFactory::class, fn () => new AgentFactory($this->loadOptions()));
        $this->loadConfiguration();
        $this->app->get(AgentFactory::class)->build();
        $this->loadRoutes();
    }

    /**
     * @return RouteCollection
     */
    public function loadRoutes()
    {
        $prefix = '/forest';

        foreach (AgentRouter::getRoutes() as $agentRoute) {
            $this->app['router']->addRoute($this->transformMethodsValuesToUpper($agentRoute['methods']), $prefix . $agentRoute['uri'], $agentRoute['closure']);
        }
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
        if (file_exists(base_path() . '/forest/symfony_forest_admin.php')) {
            $callback = require base_path() . '/forest/symfony_forest_admin.php';
            $callback($this);
        }
    }

    private function loadOptions(): array
    {
        return [
            'debug'                => env('FOREST_DEBUG', true),
            'authSecret'           => env('FOREST_AUTH_SECRET'),
            'envSecret'            => env('FOREST_ENV_SECRET'),
            'forestServerUrl'      => env('FOREST_SERVER_URL', 'https://api.forestadmin.com'),
            'isProduction'         => env('APP_ENV', 'dev') === 'prod',
            'prefix'               => env('FOREST_PREFIX', 'forest'),
            'permissionExpiration' => env('FOREST_PERMISSIONS_EXPIRATION_IN_SECONDS', 1),
            'cacheDir'             => storage_path('framework/cache/data/forest'),
            'schemaPath'           => base_path() . '/.forestadmin-schema.json',
            'projectDir'           => base_path(),
        ];
    }
}
