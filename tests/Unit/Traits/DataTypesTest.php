<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Schema\Concerns\DataTypes;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class DataTypesTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class DataTypesTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetType(): void
    {
        $trait = $this->getObjectForTrait(DataTypes::class);
        $types = $this->invokeProperty($trait, 'dbTypes');

        foreach ($types as $key => $value) {
            $getType = $this->invokeMethod($trait, 'getType', [$key]);

            $this->assertEquals($value, $getType);
        }
    }
}
