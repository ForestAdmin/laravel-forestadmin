<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\RoutesConfiguration;

/**
 * Class RoutesConfigurationTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RoutesConfigurationTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetController(): void
    {
        $trait = $this->getObjectForTrait(RoutesConfiguration::class);
        $list = $this->invokeProperty($trait, 'routesList');

        foreach ($list as $key => $value) {
            $getController = $this->invokeMethod($trait, 'getController', [$key]);

            $this->assertEquals($value, $getController);
        }
    }
}
