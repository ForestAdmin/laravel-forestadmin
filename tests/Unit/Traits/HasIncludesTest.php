<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasIncludes;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class HasIncludesTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasIncludesTest extends TestCase
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAddInclude(): void
    {
        $trait = $this->getObjectForTrait(HasIncludes::class);

        $values = $this->invokeMethod($trait, 'addInclude', ['foo', ['a', 'b', 'c'], 'foo_key']);

        $this->assertIsArray($values->getIncludes());
        $this->assertEquals(['fields' => 'a,b,c', 'foreign_key' => 'foo_key'], $values->getIncludes()['foo']);
    }
}
