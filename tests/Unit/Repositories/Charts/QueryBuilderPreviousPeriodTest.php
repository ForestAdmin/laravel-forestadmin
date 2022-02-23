<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Carbon\Carbon;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns\QueryBuilderPreviousPeriod;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Mockery as m;

/**
 * Class QueryBuilderPreviousPeriodTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class QueryBuilderPreviousPeriodTest extends TestCase
{
    use ScopeManagerFactory;

    /**
     * @return void
     * @throws \JsonException
     */
    public function setUp(): void
    {
        parent::setUp();

        $forestUser = new ForestUser(
            [
                'id'           => 1,
                'email'        => 'john.doe@forestadmin.com',
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'rendering_id' => 1,
                'tags'         => [],
                'teams'        => 'Operations',
                'exp'          => 1643825269,
            ]
        );
        //--- push instance of ScopeManager in App ---//
        $this->makeScopeManager($forestUser);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersTodayOperator(): void
    {
        $operator = 'today';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDay()->startOfDay(),
                        (new Carbon('now', $timezone))->subDay()->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDay()->startOfDay(),
                        (new Carbon('now', $timezone))->subDay()->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousXdaysOperator(): void
    {
        $operator = 'previous_x_days';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData(['Date', 'Dateonly'], 3);
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDays($data['Date']['value'] * 2)->startOfDay(),
                        (new Carbon('now', $timezone))->subDays(2)->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDays($data['Dateonly']['value'] * 2)->startOfDay(),
                        (new Carbon('now', $timezone))->subDays(2)->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousXdaysToDateOperator(): void
    {
        $operator = 'previous_x_days_to_date';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData(['Date', 'Dateonly'], 3);
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDays($data['Date']['value'] * 2)->startOfDay(),
                        (new Carbon('now', $timezone))->subDay()->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDays($data['Dateonly']['value'] * 2)->startOfDay(),
                        (new Carbon('now', $timezone))->subDay()->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersYesterdayOperator(): void
    {
        $operator = 'yesterday';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDays(2)->startOfDay(),
                        (new Carbon('now', $timezone))->subDays(2)->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subDays(2)->startOfDay(),
                        (new Carbon('now', $timezone))->subDays(2)->endOfDay(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($expected, $actual, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousWeekOperator(): void
    {
        $operator = 'previous_week';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subWeeks(2)->startOfWeek(),
                        (new Carbon('now', $timezone))->subWeeks(2)->endOfWeek(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subWeeks(2)->startOfWeek(),
                        (new Carbon('now', $timezone))->subWeeks(2)->endOfWeek(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($expected, $actual, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousMonthOperator(): void
    {
        $operator = 'previous_month';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subMonths(2)->startOfMonth(),
                        (new Carbon('now', $timezone))->subMonths(2)->endOfMonth(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subMonths(2)->startOfMonth(),
                        (new Carbon('now', $timezone))->subMonths(2)->endOfMonth(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($expected, $actual, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousQuarterOperator(): void
    {
        $operator = 'previous_quarter';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subQuarters(2)->startOfQuarter(),
                        (new Carbon('now', $timezone))->subQuarters(2)->endOfQuarter(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subQuarters(2)->startOfQuarter(),
                        (new Carbon('now', $timezone))->subQuarters(2)->endOfQuarter(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($expected, $actual, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousYearOperator(): void
    {
        $operator = 'previous_year';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subYears(2)->startOfYear(),
                        (new Carbon('now', $timezone))->subYears(2)->endOfYear(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subYears(2)->startOfYear(),
                        (new Carbon('now', $timezone))->subYears(2)->endOfYear(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($expected, $actual, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousWeekToDateOperator(): void
    {
        $operator = 'previous_week_to_date';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subWeeks(2)->startOfWeek(),
                        (new Carbon('now', $timezone))->subWeek(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subWeeks(2)->startOfWeek(),
                        (new Carbon('now', $timezone))->subWeek(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
            $this->assertEquals($expected['type'], $actual['type'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['column'], $actual['column'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['boolean'], $actual['boolean'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['not'], $actual['not'], 'error on type ' . $value['type']);
            $this->assertIsArray($actual['values'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][0], $actual['values'][0], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][1]->format('Y-m-d H:i'), $actual['values'][1]->format('Y-m-d H:i'), 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousMonthToDateOperator(): void
    {
        $operator = 'previous_month_to_date';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subMonths(2)->startOfMonth(),
                        (new Carbon('now', $timezone))->subMonth(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subMonths(2)->startOfMonth(),
                        (new Carbon('now', $timezone))->subMonth(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
            $this->assertEquals($expected['type'], $actual['type'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['column'], $actual['column'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['boolean'], $actual['boolean'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['not'], $actual['not'], 'error on type ' . $value['type']);
            $this->assertIsArray($actual['values'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][0], $actual['values'][0], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][1]->format('Y-m-d H:i'), $actual['values'][1]->format('Y-m-d H:i'), 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousQuarterToDateOperator(): void
    {
        $operator = 'previous_quarter_to_date';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subQuarters(2)->startOfQuarter(),
                        (new Carbon('now', $timezone))->subQuarter(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subQuarters(2)->startOfQuarter(),
                        (new Carbon('now', $timezone))->subQuarter(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
            $this->assertEquals($expected['type'], $actual['type'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['column'], $actual['column'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['boolean'], $actual['boolean'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['not'], $actual['not'], 'error on type ' . $value['type']);
            $this->assertIsArray($actual['values'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][0], $actual['values'][0], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][1]->format('Y-m-d H:i'), $actual['values'][1]->format('Y-m-d H:i'), 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDateFiltersPreviousYearToDateOperator(): void
    {
        $operator = 'previous_year_to_date';
        $timezone = new \DateTimeZone('UTC');
        $data = $this->getData();
        $results = [
            'Date'     => [
                [
                    'type'    => 'between',
                    'column'  => 'created_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subYears(2)->startOfYear(),
                        (new Carbon('now', $timezone))->subYear(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
            'Dateonly' => [
                [
                    'type'    => 'between',
                    'column'  => 'published_at',
                    'values'  => [
                        (new Carbon('now', $timezone))->subYears(2)->startOfYear(),
                        (new Carbon('now', $timezone))->subYear(),
                    ],
                    'boolean' => 'and',
                    'not'     => false,
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilderPreviousPeriod::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'dateFilters',
                [$queryBuilder->query(), $value['field'], $operator, null, $value['type']]
            );
            $expected = $results[$value['type']][0];
            $actual = $queryResult->getQuery()->wheres[0];

            $this->assertIsArray($queryResult->getQuery()->wheres, 'error on type ' . $value['type']);
            $this->assertEquals($expected['type'], $actual['type'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['column'], $actual['column'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['boolean'], $actual['boolean'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['not'], $actual['not'], 'error on type ' . $value['type']);
            $this->assertIsArray($actual['values'], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][0], $actual['values'][0], 'error on type ' . $value['type']);
            $this->assertEquals($expected['values'][1]->format('Y-m-d H:i'), $actual['values'][1]->format('Y-m-d H:i'), 'error on type ' . $value['type']);
        }
    }

    /**
     * @param array|null  $types
     * @param string|null $overrideValue
     * @return array
     */
    protected function getData(?array $types = null, ?string $overrideValue = null): array
    {
        $collection = collect(
            [
                'Date'     => [
                    'type'  => 'Date',
                    'field' => 'created_at',
                    'value' => $overrideValue ?? '2022-01-01 12:00:00',
                ],
                'Dateonly' => [
                    'type'  => 'Dateonly',
                    'field' => 'published_at',
                    'value' => $overrideValue ?? '2022-01-01',
                ],
            ]
        );

        return $collection->reject(fn($value, $key) => $types && !in_array($value['type'], $types))->all();
    }
}
