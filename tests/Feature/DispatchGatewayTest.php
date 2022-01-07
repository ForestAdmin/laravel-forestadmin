<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\DispatchGateway;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\Facades\Route;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class DispatchGatewayTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class DispatchGatewayTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testGetAction(): void
    {
        $dispatch = $this->makeDispatcher();

        $route = Route::any(
            'forest/foo',
            ['as' => 'forest.collection.foo_bar']
        );

        $action = $dispatch->getAction($route);

        $this->assertEquals('fooBar', $action);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRouteNameIsValid(): void
    {
        $routeName = 'forest.collection.foo';
        $dispatch = $this->makeDispatcher();

        $isValid = $this->invokeMethod($dispatch, 'routeNameIsValid', [$routeName]);
        $this->assertTrue($isValid);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRouteNameIsValidNullRouteNameException(): void
    {
        $routeName = null;
        $dispatch = $this->makeDispatcher();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 You must define a name for the route');

        $this->invokeMethod($dispatch, 'routeNameIsValid', [$routeName]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRouteNameIsValidFormatRouteNameException(): void
    {
        $routeName = 'foo';
        $dispatch = $this->makeDispatcher();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 The route name must have 3 parameters and start with `forest.`');

        $this->invokeMethod($dispatch, 'routeNameIsValid', [$routeName]);
    }

    /**
     * @return DispatchGateway
     */
    public function makeDispatcher(): DispatchGateway
    {
        $dispatcher = $this->prophesize(ControllerDispatcher::class)->reveal();
        return new DispatchGateway($dispatcher);
    }
}
