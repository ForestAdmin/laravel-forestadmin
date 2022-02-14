<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Objective;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
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
    use FakeData;

    /**
     * @return void
     */
    public function testGet(): void
    {
        $this->getBook()->save();
        $repository = m::mock(Objective::class, [Book::first()])
            ->makePartial();
        $serialize = $repository->serialize(10);

        $this->assertIsArray($serialize);
        $this->assertEquals(['value' => 10], $serialize);
    }
}
