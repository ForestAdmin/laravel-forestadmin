<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\RoutesConfiguration;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * Class DispatchGateway
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class DispatchGateway
{
    use RoutesConfiguration;

    /**
     * @var ControllerDispatcher
     */
    private ControllerDispatcher $controllerDispatcher;

    /**
     * @param ControllerDispatcher $controllerDispatcher
     */
    public function __construct(ControllerDispatcher $controllerDispatcher)
    {
        $this->controllerDispatcher = $controllerDispatcher;
    }

    /**
     * @param Route $route
     * @return mixed
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function __invoke(Route $route)
    {
        $action = $this->getAction($route);
        $controller = app()->make($this->getController($route->getName()));

        return $this->controllerDispatcher->dispatch($route, $controller, $action);
    }

    /**
     * @param Route $route
     * @return string
     */
    public function getAction(Route $route): string
    {
        $this->routeNameIsValid($route->getName());
        $data = explode('.', $route->getName());

        return Str::camel($data[2]);
    }

    /**
     * @param string|null $routeName
     * @return bool
     */
    private function routeNameIsValid(?string $routeName = null): bool
    {
        if (null === $routeName) {
            throw new ForestException('You must define a name for the route');
        }

        $data = explode('.', $routeName);
        if (count($data) !== 3 || !Str::startsWith($routeName, 'forest.')) {
            throw new ForestException('The route name must have 3 parameters and start with `forest.`)');
        }

        return true;
    }
}
