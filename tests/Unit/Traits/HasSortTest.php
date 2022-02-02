<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasSort;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Mockery as m;

/**
 * Class HasSortTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasSortTest extends TestCase
{
    /**
     * @return void
     */
    public function testSortByAndDirection(): void
    {
        $trait = $this->getObjectForTrait(HasSort::class);
        $sortByAsc = 'foo';
        $sortByDesc = '-foo';

        $orderAsc = $trait->sortByAndDirection($sortByAsc);
        $orderDesc = $trait->sortByAndDirection($sortByDesc);

        $this->assertIsArray($orderAsc);
        $this->assertIsArray($orderDesc);
        $this->assertEquals(['foo', 'ASC'], $orderAsc);
        $this->assertEquals(['foo', 'DESC'], $orderDesc);
    }
}
