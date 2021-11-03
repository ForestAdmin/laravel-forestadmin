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
    }

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $config = $app['config'];
        $config->set('app.debug', true);
        $config->set('forest.api.secret', 'my-secret-key');
        $config->set('forest.api.auth-secret', 'auth-secret-key');
    }

    /**
     * Call protected/private method of a class.
     * @param object $object
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
     * Call protected/private property of a class.
     * @param object $object
     * @param string $methodName
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeProperty(object &$object, string $methodName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($methodName);
        $property->setAccessible(true);

        return $property->getValue($object);
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
