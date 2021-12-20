<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\ArrayHelper;

/**
 * Class ArrayHelperTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ArrayHelperTest extends TestCase
{
    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testMergeArray(): void
    {
        $trait = $this->getObjectForTrait(ArrayHelper::class);

        $array = [1, 2, 3];
        $addValue = $this->invokeMethod($trait, 'mergeArray', [$array, 4]);
        $valueExist = $this->invokeMethod($trait, 'mergeArray', [$array, 1]);
        $addValues = $this->invokeMethod($trait, 'mergeArray', [$array, [1, 5, 6]]);

        $this->assertEquals([1, 2, 3, 4], $addValue);
        $this->assertEquals([1, 2, 3], $valueExist);
        $this->assertEquals([1, 2, 3, 5, 6], $addValues);
    }
}
