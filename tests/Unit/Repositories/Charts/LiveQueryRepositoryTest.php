<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Mockery as m;

/**
 * Class LiveQueryRepositoryTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class LiveQueryRepositoryTest extends TestCase
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testChartWithRecordId(): void
    {
        $type = 'Value';
        $query = "select count('*') as value from books where category_id = ?";
        $recordId = 1;
        $queryResult = [
            (object) ['value' => 10],
        ];
        $queryBinding = "select count('*') as value from books where category_id = $recordId";

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', $recordId);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $this->invokeMethod($liveQueryRepository, 'get');
        $rawQuery = $this->invokeProperty($liveQueryRepository, 'rawQuery');

        $this->assertEquals($queryBinding, $rawQuery);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeValueChart(): void
    {
        $type = 'Value';
        $query = "select count('*') as value from books where books.active = true";
        $queryResult = [
            (object) ['value' => 10],
        ];
        $expected = [
            'countCurrent'  => 10,
            'countPrevious' => null,
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $result = $this->invokeMethod($liveQueryRepository, 'get');
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeValueChartException(): void
    {
        $type = 'Value';
        $query = "select count('*') as item from books where books.active = true";
        $queryResult = [
            (object) ['item' => 10],
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The result columns must be named ''value'' instead of 'item'");
        $this->invokeMethod($liveQueryRepository, 'get');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeObjectiveChart(): void
    {
        $type = 'Objective';
        $query = "select count(*) as value, 200 as objective from books";
        $queryResult = [
            (object) [
                'value'     => 10,
                'objective' => 100,
            ],
        ];
        $expected = [
            'value'     => 10,
            'objective' => 100,
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $result = $this->invokeMethod($liveQueryRepository, 'get');
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeObjectiveChartException(): void
    {
        $type = 'Value';
        $query = "select count(*) as value, 200 as objective from books";
        $queryResult = [
            (object) [
                'item'     => 10,
                'objective' => 100,
            ],
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The result columns must be named ''value'' instead of 'item,objective'");
        $this->invokeMethod($liveQueryRepository, 'get');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializePieChart(): void
    {
        $type = 'Pie';
        $query = "select COUNT(categories.label) as value, categories.label as key from books 
                    inner join categories on books.category_id = categories.id group by categories.label";
        $queryResult = [
            (object) [
                'value' => 10,
                'key'   => 'foo',
            ],
            (object) [
                'value' => 15,
                'key'   => 'test',
            ],
            (object) [
                'value' => 20,
                'key'   => 'Doe',
            ],
        ];
        $expected = $queryResult;

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $result = $this->invokeMethod($liveQueryRepository, 'get');
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializePieChartException(): void
    {
        $type = 'Pie';
        $query = "select COUNT(categories.label) as value, categories.label as item from books 
                    inner join categories on books.category_id = categories.id group by categories.label";
        $queryResult = [
            (object) [
                'value' => 10,
                'item'   => 'foo',
            ],
            (object) [
                'value' => 15,
                'item'   => 'test',
            ],
            (object) [
                'value' => 20,
                'item'   => 'Doe',
            ],
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The result columns must be named ''key', 'value'' instead of 'value,item'");
        $this->invokeMethod($liveQueryRepository, 'get');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeLineChart(): void
    {
        $type = 'Line';
        $query = "select count(*) as value, to_char(created_at, 'yyyy-mm-dd') as key from books group by key";
        $queryResult = [
            (object) [
                'value' => 10,
                'key'   => '2022-02-10',
            ],
            (object) [
                'value' => 15,
                'key'   => '2022-02-09',
            ],
        ];
        $expected = [
            [
                'label'  => '2022-02-10',
                'values' => [
                    'value' => 10,
                ],
            ],
            [
                'label'  => '2022-02-09',
                'values' => [
                    'value' => 15,
                ],
            ],
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $result = $this->invokeMethod($liveQueryRepository, 'get');
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeLineChartException(): void
    {
        $type = 'Line';
        $query = "select count(*) as value, to_char(created_at, 'yyyy-mm-dd') as item from books group by key";
        $queryResult = [
            (object) [
                'value' => 10,
                'item'   => '2022-02-10',
            ],
            (object) [
                'value' => 15,
                'item'   => '2022-02-09',
            ],
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The result columns must be named ''key', 'value'' instead of 'value,item'");
        $this->invokeMethod($liveQueryRepository, 'get');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeLeaderboardChart(): void
    {
        $type = 'Leaderboard';
        $query = "select books.label as key, count(c.id) as value from books 
                    left join comments c on books.id = c.book_id GROUP BY books.label LIMIT 10";
        $queryResult = [
            (object) [
                'value' => 2,
                'key'   => 'Ms. Felicita Cartwright I',
            ],
            (object) [
                'value' => 2,
                'key'   => 'Antonina Lubowitz',
            ],
        ];
        $expected = $queryResult;

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $result = $this->invokeMethod($liveQueryRepository, 'get');
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerializeLeaderboardChartException(): void
    {
        $type = 'Leaderboard';
        $query = "select books.label as item, count(c.id) as value from books 
                    left join comments c on books.id = c.book_id GROUP BY books.label LIMIT 10";
        $queryResult = [
            (object) [
                'value' => 2,
                'item'  => 'Ms. Felicita Cartwright I',
            ],
            (object) [
                'value' => 2,
                'item'  => 'Antonina Lubowitz',
            ],
        ];

        $liveQueryRepository = m::mock('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $type)->makePartial();
        $this->invokeProperty($liveQueryRepository, 'type', $type);
        $this->invokeProperty($liveQueryRepository, 'rawQuery', $query);
        $this->invokeProperty($liveQueryRepository, 'recordId', null);
        $liveQueryRepository->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateQuery')
            ->andReturn(null);
        DB::shouldReceive('select')
            ->set('query', $query)
            ->andReturn($queryResult);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The result columns must be named ''key', 'value'' instead of 'value,item'");
        $this->invokeMethod($liveQueryRepository, 'get');
    }
}
