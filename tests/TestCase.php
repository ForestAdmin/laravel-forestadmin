<?php

namespace ForestAdmin\LaravelForestAdmin\Tests;

use ForestAdmin\LaravelForestAdmin\ForestServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Class TestCase
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        config('forest.api.secret', 'my-secret-key');
    }

    /**
     * Call protected/private method of a class.
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeMethod(object &$object, string $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ForestServiceProvider::class,
        ];
    }
}
