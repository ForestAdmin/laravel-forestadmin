<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Facades\Cache;
use ForestAdmin\AgentPHP\Agent\Http\Router as AgentRouter;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\ForestController;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\ServiceProvider;

class AgentProvider extends ServiceProvider
{
    protected RouteCollection $routes;

    public function boot()
    {
        if ($this->forestIsPlanted() && (request()->getMethod() !== 'OPTIONS')) {
            self::getAgentInstance();

            $this->loadConfiguration();
        }
    }

    public static function getAgentInstance()
    {
        if (Cache::enabled() && Cache::has('forestAgent')) {
            return Cache::get('forestAgent');
        } else {
            $agent = new AgentFactory(config('forest'));
            Cache::put('forestAgent', $agent, 3600);

            return $agent;
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
        return config('forest.authSecret') && config('forest.envSecret');
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
        if (file_exists($this->appForestConfig())) {
            $hash = sha1(file_get_contents($this->appForestConfig()));
            if($hash !== Cache::get('forestConfigHash')) {
                $callback = require $this->appForestConfig();
                $callback();
                Cache::put('forestConfigHash', $hash, 3600);
            }
            $this->loadRoutes();
        }
    }

    private function appForestConfig(): string
    {
        return base_path() . DIRECTORY_SEPARATOR . 'forest' . DIRECTORY_SEPARATOR . 'forest_admin.php';
    }
}
