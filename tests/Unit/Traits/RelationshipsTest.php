<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Schema\Concerns\Relationships;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class DataTypesTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RelationshipsTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testMapRelationships(): void
    {
        $trait = $this->getObjectForTrait(Relationships::class);
        $types = $this->invokeProperty($trait, 'doctrineTypes');

        foreach ($types as $key => $value) {
            $getType = $this->invokeMethod($trait, 'mapRelationships', [$key]);

            $this->assertEquals($types[$key], $getType);
        }
    }
}
