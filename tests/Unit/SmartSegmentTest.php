<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartSegment;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class SmartSegmentTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartSegmentTest extends TestCase
{
    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $smartSegment = new SmartSegment('Book', 'foo', 'makeFoo', fn() => 'test');
        $serialize = $smartSegment->serialize();

        $result = [
            'id'         => 'Book.foo',
            'name'       => 'foo',
            'methodName' => 'makeFoo',
        ];

        $this->assertIsArray($serialize);
        $this->assertEquals($result, $serialize);
    }

    /**
     * @return void
     */
    public function testGetExecute(): void
    {
        $smartSegment = new SmartSegment('Book', 'foo', 'makeFoo', fn() => 'test');
        $execute = $smartSegment->getExecute();

        $this->assertInstanceOf(\Closure::class, $execute);
        $this->assertEquals('test', call_user_func($execute));
    }
}
