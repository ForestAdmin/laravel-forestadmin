<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns\ChartHelper;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class ChartHelperTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartHelperTest extends TestCase
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAbortIfOnCollection(): void
    {
        $trait = $this->getObjectForTrait(ChartHelper::class);
        $data = collect(
            ['key' => 'foo_key',  'item' => 'foo_value']
        );

        $result = $this->invokeMethod($trait, 'abortIf', [false, $data, "key, item"]);

        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAbortIfOnArray(): void
    {
        $trait = $this->getObjectForTrait(ChartHelper::class);
        $data = ['key' => 'foo_key',  'item' => 'foo_value'];

        $result = $this->invokeMethod($trait, 'abortIf', [false, $data, "key, item"]);

        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAbortIfException(): void
    {
        $trait = $this->getObjectForTrait(ChartHelper::class);
        $data = collect(
            ['key' => 'foo_key',  'item' => 'foo_value']
        );

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 The result columns must be named \'key, value\' instead of \'key,item\'');

        $this->invokeMethod($trait, 'abortIf', [true, $data, "key, value"]);
    }
}
