<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestCors;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

/**
 * Class AuthControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestCorsTest extends TestCase
{
    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $this->addRoutes($app['router']);

        parent::getEnvironmentSetUp($app);
    }

    /**
     * @return void
     */
    public function testShouldReturnHeadersPrivateNetworkOnPreflightRequest(): void
    {
        $response = $this->withHeaders(
            [
                'Access-Control-Request-Private-Network' => 'true',
                'Access-Control-Request-Method'          => 'true',
                'HTTP_ORIGIN'                            => 'http://api.forestadmin.com',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD'     => 'POST',
            ]
        )
            ->options('forest/ping');

        $this->assertEquals('http://api.forestadmin.com', $response->headers->get('access-control-allow-origin'));
        $this->assertEquals('true', $response->headers->get('access-control-allow-credentials'));
        $this->assertTrue(Str::containsAll($response->headers->get('access-control-allow-methods'), ['GET', 'POST', 'PUT', 'DELETE']));
        $this->assertEquals(86400, $response->headers->get('access-control-max-age'));
        $this->assertNull($response->headers->get('access-control-allow-headers'));
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Private-Network'));
        $this->assertEquals(204, $response->getStatusCode());
    }


    /**
     * @return void
     */
    public function testShouldReturnHeadersOnPreflightRequest(): void
    {
        $response = $this->call(
            'OPTIONS',
            'forest/ping',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN'                        => 'http://api.forestadmin.com',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            ]
        );

        $this->assertEquals('http://api.forestadmin.com', $response->headers->get('access-control-allow-origin'));
        $this->assertEquals('true', $response->headers->get('access-control-allow-credentials'));
        $this->assertTrue(Str::containsAll($response->headers->get('access-control-allow-methods'), ['GET', 'POST', 'PUT', 'DELETE']));
        $this->assertEquals(86400, $response->headers->get('access-control-max-age'));
        $this->assertEquals(null, $response->headers->get('access-control-allow-headers'));
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testShouldReturnHeadersOnOptionsRequest(): void
    {
        $response = $this->call(
            'OPTIONS',
            'forest/ping',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://api.forestadmin.com',
            ]
        );

        $this->assertEquals('Access-Control-Request-Method, Origin', $response->headers->get('vary'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testShouldReturnHeadersOnMethodAllowed(): void
    {
        $response = $this->call(
            'OPTIONS',
            'forest/ping',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://api.forestadmin.com',
            ]
        );

        $this->assertEquals('http://api.forestadmin.com', $response->headers->get('access-control-allow-origin'));
        $this->assertEquals('true', $response->headers->get('access-control-allow-credentials'));
        $this->assertEquals('Access-Control-Request-Method, Origin', $response->headers->get('vary'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testShouldNotReturnHeadersOnOriginNotAllowed(): void
    {
        $response = $this->call(
            'POST',
            'forest/ping',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN'                        => 'http://example.com',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            ]
        );

        $this->assertEquals(null, $response->headers->get('access-control-allow-credentials'));
        $this->assertEquals(null, $response->headers->get('access-control-allow-methods'));
        $this->assertEquals(null, $response->headers->get('access-control-max-age'));
        $this->assertEquals(null, $response->headers->get('access-control-allow-headers'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testShouldNotRunOnRouteNotPrefixWithForest(): void
    {
        $response = $this->call(
            'POST',
            'no-forest/ping',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN'                        => 'http://api.forestadmin.com',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            ]
        );

        $this->assertEquals(null, $response->headers->get('access-control-allow-credentials'));
        $this->assertEquals(null, $response->headers->get('access-control-allow-methods'));
        $this->assertEquals(null, $response->headers->get('access-control-max-age'));
        $this->assertEquals(null, $response->headers->get('access-control-allow-headers'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testCoreOptions(): void
    {
        $configOptions = [
            'allowedHeaders'         => ['*'],
            'allowedMethods'         => ['GET', 'POST', 'PUT', 'DELETE'],
            'allowedOrigins'         => ['*.forestadmin.com', 'localhost:(\d){4}'],
            'allowedOriginsPatterns' => ['#^.*\.forestadmin\.com\z#u'],
            'exposedHeaders'         => false,
            'maxAge'                 => 86400,
            'supportsCredentials'    => true,
        ];
        $middleware = app(ForestCors::class);
        $options = $this->invokeMethod($middleware, 'getCorsOptions');


        $this->assertEquals($configOptions['allowedHeaders'][0], $options['allowedHeaders'][0]);
        $this->assertEquals($configOptions['allowedMethods'][0], $options['allowedMethods'][0]);
        $this->assertEquals($configOptions['allowedOrigins'][0], $options['allowedOrigins'][0]);
        $this->assertEquals($configOptions['allowedOriginsPatterns'][0], $options['allowedOriginsPatterns'][0]);
        $this->assertEquals($configOptions['exposedHeaders'], $options['exposedHeaders']);
        $this->assertEquals($configOptions['maxAge'], $options['maxAge']);
        $this->assertEquals($configOptions['supportsCredentials'], $options['supportsCredentials']);
    }


    /**
     * @param Router $router
     * @return void
     */
    protected function addRoutes(Router $router): void
    {
        $router->any(
            'forest/ping',
            ['uses' => fn() => 'PONG']
        );

        $router->any(
            'no-forest/ping',
            ['uses' => fn() => 'PONG']
        );
    }
}
