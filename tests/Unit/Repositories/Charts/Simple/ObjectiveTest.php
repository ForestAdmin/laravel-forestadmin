<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Objective;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Mockery as m;

/**
 * Class ObjectiveTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ObjectiveTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet(): void
    {
        $repository = m::mock(Objective::class, [new Book()])
            ->makePartial();
        $serialize = $repository->serialize(10);

        $this->assertIsArray($serialize);
        $this->assertEquals(['value' => 10], $serialize);
    }
}
