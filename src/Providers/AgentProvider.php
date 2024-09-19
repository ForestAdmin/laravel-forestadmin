<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Facades\Cache;
use ForestAdmin\AgentPHP\Agent\Http\Router as AgentRouter;
use ForestAdmin\AgentPHP\Agent\Utils\Filesystem;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\ForestController;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\ServiceProvider;
use Laravel\SerializableClosure\SerializableClosure;

class AgentProvider extends ServiceProvider
{
    protected RouteCollection $routes;

    public function boot()
    {
        if ($this->forestIsPlanted() && (request()->getMethod() !== 'OPTIONS')) {
            // set cache file configuration
            $filesystem = new Filesystem();
            $directory = config('forest')['cacheDir'];
            $disabledApcuCache = config('forest')['disabledApcuCache'] ?? true;
            AgentFactory::$fileCacheOptions = compact('filesystem', 'directory', 'disabledApcuCache');

            $this->app->instance(AgentFactory::class, self::getAgentInstance());

            $this->loadConfiguration();
        }
    }

    public static function getAgentInstance(bool $forceReload = false)
    {
        if (! $forceReload &&
            Cache::enabled() &&
            Cache::has('forestAgent') &&
            Cache::get('forestAgentExpireAt') > strtotime('+ 30 seconds')
        ) {
            $forestAgentClosure = Cache::get('forestAgent');

            return $forestAgentClosure();
        }

        $agent = new AgentFactory(config('forest'));
        $expireAt = strtotime('+ '. AgentFactory::TTL .' seconds');
        Cache::put('forestAgent', new SerializableClosure(fn () => $agent), AgentFactory::TTL);
        Cache::put('forestAgentExpireAt', $expireAt, AgentFactory::TTL);

        return $agent;
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
            $agent = self::getAgentInstance();
            if($hash !== Cache::get('forestConfigHash') || AgentFactory::get('datasource') === null) {
                $this->app->instance(AgentFactory::class, self::getAgentInstance(true));
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
