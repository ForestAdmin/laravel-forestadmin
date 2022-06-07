<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Mockery as m;

/**
 * Class HasFiltersOperatorsTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasFiltersOperatorsTest extends TestCase
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
    public function testMainFiltersPresentOperator(): void
    {
        $operator = 'present';
        $data = $this->getData();
        $results = [
            'Date'     => [
                ['type' => 'NotNull', 'column' => 'created_at', 'boolean' => 'and',],
            ],
            'Dateonly' => [
                ['type' => 'NotNull', 'column' => 'published_at', 'boolean' => 'and',],
            ],
            'Boolean'  => [
                ['type' => 'NotNull', 'column' => 'active', 'boolean' => 'and',],
            ],
            'Enum'     => [
                ['type' => 'NotNull', 'column' => 'difficulty', 'boolean' => 'and',],
                ['type' => 'Basic', 'column' => 'difficulty', 'operator' => '!=', 'value' => '', 'boolean' => 'or',],
            ],
            'Number'   => [
                ['type' => 'NotNull', 'column' => 'amount', 'boolean' => 'and',],
            ],
            'String'   => [
                ['type' => 'NotNull', 'column' => 'label', 'boolean' => 'and',],
                ['type' => 'Basic', 'column' => 'label', 'operator' => '!=', 'value' => '', 'boolean' => 'or',],
            ],
            'Uuid'     => [
                ['type' => 'NotNull', 'column' => 'token', 'boolean' => 'and',],
            ],
            'Time'     => [
                ['type' => 'NotNull', 'column' => 'delivery_hour', 'boolean' => 'and',],
                ['type' => 'Basic', 'column' => 'delivery_hour', 'operator' => '!=', 'value' => '', 'boolean' => 'or',],
            ],
            'Json'     => [
                ['type' => 'NotNull', 'column' => 'options', 'boolean' => 'and',],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertArrayHasKey('query', $queryResult->getQuery()->wheres[0]);
            $nestedQueryResult = $queryResult->getQuery()->wheres[0]['query'];
            $this->assertIsArray($nestedQueryResult->wheres);
            $this->assertEquals($results[$value['type']], $nestedQueryResult->wheres, 'error on type ' . $value['type']);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersBlankOperator(): void
    {
        $operator = 'blank';
        $data = $this->getData();
        $results = [
            'Date'     => [
                ['type' => 'Null', 'column' => 'created_at', 'boolean' => 'and'],
            ],
            'Dateonly' => [
                ['type' => 'Null', 'column' => 'published_at', 'boolean' => 'and',],
            ],
            'Boolean'  => [
                ['type' => 'Null', 'column' => 'active', 'boolean' => 'and',],
            ],
            'Enum'     => [
                ['type' => 'Null', 'column' => 'difficulty', 'boolean' => 'and',],
                ['type' => 'Basic', 'column' => 'difficulty', 'operator' => '=', 'value' => '', 'boolean' => 'or',],
            ],
            'Number'   => [
                ['type' => 'Null', 'column' => 'amount', 'boolean' => 'and',],
            ],
            'String'   => [
                ['type' => 'Null', 'column' => 'label', 'boolean' => 'and',],
                ['type' => 'Basic', 'column' => 'label', 'operator' => '=', 'value' => '', 'boolean' => 'or',],
            ],
            'Uuid'     => [
                ['type' => 'Null', 'column' => 'token', 'boolean' => 'and',],
            ],
            'Time'     => [
                ['type' => 'Null', 'column' => 'delivery_hour', 'boolean' => 'and',],
                ['type' => 'Basic', 'column' => 'delivery_hour', 'operator' => '=', 'value' => '', 'boolean' => 'or',],
            ],
            'Json'     => [
                ['type' => 'Null', 'column' => 'options', 'boolean' => 'and',],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertArrayHasKey('query', $queryResult->getQuery()->wheres[0]);
            $nestedQueryResult = $queryResult->getQuery()->wheres[0]['query'];
            $this->assertIsArray($nestedQueryResult->wheres);
            $this->assertEquals($results[$value['type']], $nestedQueryResult->wheres, 'Result for type ' . $value['type'] . ' is incorrect.');
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersContainsOperator(): void
    {
        $operator = 'contains';
        $data = $this->getData(['String']);
        $results = [
            'String' => [
                'wheres'   => [
                    [
                        'type'    => 'raw',
                        'sql'     => 'LOWER (label) LIKE LOWER(?)',
                        'boolean' => 'and',
                    ],
                ],
                'bindings' => ['%foo,   john,  doe%'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );


            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertIsArray($queryResult->getQuery()->bindings);
            $this->assertArrayHasKey('where', $queryResult->getQuery()->bindings);
            $this->assertEquals(
                $results[$value['type']]['wheres'],
                $queryResult->getQuery()->wheres,
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
            $this->assertEquals(
                $results[$value['type']]['bindings'],
                $queryResult->getQuery()->bindings['where'],
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersNotContainsOperator(): void
    {
        $operator = 'not_contains';
        $data = $this->getData(['String']);
        $results = [
            'String' => [
                'wheres'   => [
                    [
                        'type'    => 'raw',
                        'sql'     => 'LOWER (label) NOT LIKE LOWER(?)',
                        'boolean' => 'and',
                    ],
                ],
                'bindings' => ['%foo,   john,  doe%'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );


            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertIsArray($queryResult->getQuery()->bindings);
            $this->assertArrayHasKey('where', $queryResult->getQuery()->bindings);
            $this->assertEquals(
                $results[$value['type']]['wheres'],
                $queryResult->getQuery()->wheres,
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
            $this->assertEquals(
                $results[$value['type']]['bindings'],
                $queryResult->getQuery()->bindings['where'],
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersStartsWithOperator(): void
    {
        $operator = 'starts_with';
        $data = $this->getData(['String']);
        $results = [
            'String' => [
                'wheres'   => [
                    [
                        'type'    => 'raw',
                        'sql'     => 'LOWER (label) LIKE LOWER(?)',
                        'boolean' => 'and',
                    ],
                ],
                'bindings' => ['foo,   john,  doe%'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );


            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertIsArray($queryResult->getQuery()->bindings);
            $this->assertArrayHasKey('where', $queryResult->getQuery()->bindings);
            $this->assertEquals(
                $results[$value['type']]['wheres'],
                $queryResult->getQuery()->wheres,
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
            $this->assertEquals(
                $results[$value['type']]['bindings'],
                $queryResult->getQuery()->bindings['where'],
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersEndsWithOperator(): void
    {
        $operator = 'ends_with';
        $data = $this->getData(['String']);
        $results = [
            'String' => [
                'wheres'   => [
                    [
                        'type'    => 'raw',
                        'sql'     => 'LOWER (label) LIKE LOWER(?)',
                        'boolean' => 'and',
                    ],
                ],
                'bindings' => ['%foo,   john,  doe'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );


            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertIsArray($queryResult->getQuery()->bindings);
            $this->assertArrayHasKey('where', $queryResult->getQuery()->bindings);
            $this->assertEquals(
                $results[$value['type']]['wheres'],
                $queryResult->getQuery()->wheres,
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
            $this->assertEquals(
                $results[$value['type']]['bindings'],
                $queryResult->getQuery()->bindings['where'],
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersInOperator(): void
    {
        $operator = 'in';
        $data = $this->getData(['String', 'Enum', 'Number']);
        $results = [
            'String' => [
                [
                    'type'    => 'In',
                    'column'  => 'label',
                    'boolean' => 'and',
                    'values'  => ['foo', 'john', 'doe'],
                ],
            ],
            'Enum'   => [
                [
                    'type'    => 'In',
                    'column'  => 'difficulty',
                    'boolean' => 'and',
                    'values'  => ['easy'],
                ],
            ],
            'Number' => [
                [
                    'type'    => 'In',
                    'column'  => 'amount',
                    'boolean' => 'and',
                    'values'  => ['10'],
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals(
                $results[$value['type']],
                $queryResult->getQuery()->wheres,
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersInOperatorRejectValue(): void
    {
        $operator = 'in';
        $data = [
            [
                'type'  => 'Number',
                'field' => 'amount',
                'value' => '10, foo',
            ],
        ];
        $results = [
            'Number' => [
                [
                    'type'    => 'In',
                    'column'  => 'amount',
                    'boolean' => 'and',
                    'values'  => ['10'],
                ],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals(
                $results[$value['type']],
                $queryResult->getQuery()->wheres,
                'Result for type ' . $value['type'] . ' is incorrect.'
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersEqualOperator(): void
    {
        $operator = 'equal';
        $data = $this->getData(['Date', 'Dateonly', 'Boolean', 'Enum', 'Number', 'String', 'Uuid', 'Time']);
        $results = [
            'Date'     => [
                ['type' => 'Basic', 'column' => 'created_at', 'operator' => '=', 'value' => '2022-01-01 12:00:00', 'boolean' => 'and'],
            ],
            'Dateonly' => [
                ['type' => 'Basic', 'column' => 'published_at', 'operator' => '=', 'value' => '2022-01-01', 'boolean' => 'and'],
            ],
            'Boolean'  => [
                ['type' => 'Basic', 'column' => 'active', 'operator' => '=', 'value' => true, 'boolean' => 'and'],
            ],
            'Enum'     => [
                ['type' => 'Basic', 'column' => 'difficulty', 'operator' => '=', 'value' => 'easy', 'boolean' => 'and'],
            ],
            'Number'   => [
                ['type' => 'Basic', 'column' => 'amount', 'operator' => '=', 'value' => '10', 'boolean' => 'and'],
            ],
            'String'   => [
                ['type' => 'Basic', 'column' => 'label', 'operator' => '=', 'value' => 'foo,   john,  doe', 'boolean' => 'and'],
            ],
            'Uuid'     => [
                ['type' => 'Basic', 'column' => 'token', 'operator' => '=', 'value' => '4c4bd11b-5971-33c8-902b-4e11e79c1a2f', 'boolean' => 'and'],
            ],
            'Time'     => [
                ['type' => 'Basic', 'column' => 'delivery_hour', 'operator' => '=', 'value' => '12:00:00', 'boolean' => 'and'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'Result for type ' . $value['type'] . ' is incorrect.');
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersEqualOperatorWithFalse(): void
    {
        $operator = 'equal';
        $data = $this->getData(['Boolean']);
        $data = array_shift($data);
        $data['value'] = false;
        $results = [
            'Boolean'  => [
                ['type' => 'Basic', 'column' => 'active', 'operator' => '=', 'value' => false, 'boolean' => 'and'],
            ],
        ];
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();
        $queryBuilder->setAggregator('and');
        $queryResult = $this->invokeMethod(
            $queryBuilder,
            'mainFilters',
            [$queryBuilder->query(), $data['field'], $operator, $data['value'], $data['type']]
        );

        $this->assertIsArray($queryResult->getQuery()->wheres);
        $this->assertEquals($results[$data['type']], $queryResult->getQuery()->wheres, 'Result for type ' . $data['type'] . ' is incorrect.');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersEqualOperatorException(): void
    {
        $operator = 'equal';
        $data = $this->getInvalidData(['Date', 'Dateonly', 'Number', 'Uuid', 'Time']);

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');

            $this->expectException(ForestException::class);
            $this->expectExceptionMessage('🌳🌳🌳 The type of value \'' . $value['value'] . '\' is not compatible with the type: ' . $value['type']);
            $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersNotEqualOperator(): void
    {
        $operator = 'not_equal';
        $data = $this->getData(['Date', 'Dateonly', 'Boolean', 'Enum', 'Number', 'String', 'Uuid', 'Time']);
        $results = [
            'Date'     => [
                ['type' => 'Basic', 'column' => 'created_at', 'operator' => '!=', 'value' => '2022-01-01 12:00:00', 'boolean' => 'and'],
            ],
            'Dateonly' => [
                ['type' => 'Basic', 'column' => 'published_at', 'operator' => '!=', 'value' => '2022-01-01', 'boolean' => 'and'],
            ],
            'Boolean'  => [
                ['type' => 'Basic', 'column' => 'active', 'operator' => '!=', 'value' => true, 'boolean' => 'and'],
            ],
            'Enum'     => [
                ['type' => 'Basic', 'column' => 'difficulty', 'operator' => '!=', 'value' => 'easy', 'boolean' => 'and'],
            ],
            'Number'   => [
                ['type' => 'Basic', 'column' => 'amount', 'operator' => '!=', 'value' => '10', 'boolean' => 'and'],
            ],
            'String'   => [
                ['type' => 'Basic', 'column' => 'label', 'operator' => '!=', 'value' => 'foo,   john,  doe', 'boolean' => 'and'],
            ],
            'Uuid'     => [
                ['type' => 'Basic', 'column' => 'token', 'operator' => '!=', 'value' => '4c4bd11b-5971-33c8-902b-4e11e79c1a2f', 'boolean' => 'and'],
            ],
            'Time'     => [
                ['type' => 'Basic', 'column' => 'delivery_hour', 'operator' => '!=', 'value' => '12:00:00', 'boolean' => 'and'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'Result for type ' . $value['type'] . ' is incorrect.');
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersNotEqualOperatorWithFalse(): void
    {
        $operator = 'not_equal';
        $data = $this->getData(['Boolean']);
        $data = array_shift($data);
        $data['value'] = false;
        $results = [
            'Boolean'  => [
                ['type' => 'Basic', 'column' => 'active', 'operator' => '!=', 'value' => false, 'boolean' => 'and'],
            ],
        ];
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();
        $queryBuilder->setAggregator('and');
        $queryResult = $this->invokeMethod(
            $queryBuilder,
            'mainFilters',
            [$queryBuilder->query(), $data['field'], $operator, $data['value'], $data['type']]
        );

        $this->assertIsArray($queryResult->getQuery()->wheres);
        $this->assertEquals($results[$data['type']], $queryResult->getQuery()->wheres, 'Result for type ' . $data['type'] . ' is incorrect.');
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersNotEqualOperatorException(): void
    {
        $operator = 'not_equal';
        $data = $this->getInvalidData(['Date', 'Dateonly', 'Number', 'Uuid', 'Time']);

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');

            $this->expectException(ForestException::class);
            $this->expectExceptionMessage('🌳🌳🌳 The type of value \'' . $value['value'] . '\' is not compatible with the type: ' . $value['type']);
            $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersGreaterThanOperator(): void
    {
        $operator = 'greater_than';
        $data = $this->getData(['Number', 'Time']);
        $results = [
            'Number'   => [
                ['type' => 'Basic', 'column' => 'amount', 'operator' => '>', 'value' => '10', 'boolean' => 'and'],
            ],
            'Time'     => [
                ['type' => 'Basic', 'column' => 'delivery_hour', 'operator' => '>', 'value' => '12:00:00', 'boolean' => 'and'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'Result for type ' . $value['type'] . ' is incorrect.');
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersGreaterThanOperatorException(): void
    {
        $operator = 'greater_than';
        $data = $this->getInvalidData(['Number', 'Time']);

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');

            $this->expectException(ForestException::class);
            $this->expectExceptionMessage('🌳🌳🌳 The type of value \'' . $value['value'] . '\' is not compatible with the type: ' . $value['type']);
            $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersLessThanOperator(): void
    {
        $operator = 'less_than';
        $data = $this->getData(['Number', 'Time']);
        $results = [
            'Number'   => [
                ['type' => 'Basic', 'column' => 'amount', 'operator' => '<', 'value' => '10', 'boolean' => 'and'],
            ],
            'Time'     => [
                ['type' => 'Basic', 'column' => 'delivery_hour', 'operator' => '<', 'value' => '12:00:00', 'boolean' => 'and'],
            ],
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');
            $queryResult = $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );

            $this->assertIsArray($queryResult->getQuery()->wheres);
            $this->assertEquals($results[$value['type']], $queryResult->getQuery()->wheres, 'Result for type ' . $value['type'] . ' is incorrect.');
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersLessThanOperatorException(): void
    {
        $operator = 'less_than';
        $data = $this->getInvalidData(['Number', 'Time']);

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');

            $this->expectException(ForestException::class);
            $this->expectExceptionMessage('🌳🌳🌳 The type of value \'' . $value['value'] . '\' is not compatible with the type: ' . $value['type']);
            $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMainFiltersOperatorException(): void
    {
        $operator = 'unknown';
        $data = $this->getData(['String']);

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $queryBuilder->setAggregator('and');

            $this->expectException(ForestException::class);
            $this->expectExceptionMessage("🌳🌳🌳 Unsupported operator: $operator");
            $this->invokeMethod(
                $queryBuilder,
                'mainFilters',
                [$queryBuilder->query(), $value['field'], $operator, $value['value'], $value['type']]
            );
        }
    }

    /**
     * @param array|null $types
     * @return array
     */
    protected function getData(?array $types = null): array
    {
        $collection = collect(
            [
                [
                    'type'  => 'Date',
                    'field' => 'created_at',
                    'value' => '2022-01-01 12:00:00',
                ],
                [
                    'type'  => 'Dateonly',
                    'field' => 'published_at',
                    'value' => '2022-01-01',
                ],
                [
                    'type'  => 'Boolean',
                    'field' => 'active',
                    'value' => true,
                ],
                [
                    'type'  => 'Enum',
                    'field' => 'difficulty',
                    'value' => 'easy',
                ],
                [
                    'type'  => 'Number',
                    'field' => 'amount',
                    'value' => 10,
                ],
                [
                    'type'  => 'String',
                    'field' => 'label',
                    'value' => 'foo,   john,  doe',
                    // Add many space between foo, john and doe for the trim() test in case of IN operator
                ],
                [
                    'type'  => 'Uuid',
                    'field' => 'token',
                    'value' => '4c4bd11b-5971-33c8-902b-4e11e79c1a2f',
                ],
                [
                    'type'  => 'Time',
                    'field' => 'delivery_hour',
                    'value' => '12:00:00',
                ],
                [
                    'type'  => 'Json',
                    'field' => 'options',
                    'value' => '{}',
                ],
            ]
        );

        return $collection->reject(fn($value, $key) => $types && !in_array($value['type'], $types))->all();
    }

    /**
     * @param array|null $types
     * @return array
     */
    protected function getInvalidData(?array $types = null): array
    {
        $collection = collect(
            [
                [
                    'type'  => 'Date',
                    'field' => 'created_at',
                    'value' => 'foo',
                ],
                [
                    'type'  => 'Dateonly',
                    'field' => 'published_at',
                    'value' => 'foo',
                ],
                [
                    'type'  => 'Number',
                    'field' => 'amount',
                    'value' => 'foo',
                ],
                [
                    'type'  => 'Uuid',
                    'field' => 'token',
                    'value' => 'foo',
                ],
                [
                    'type'  => 'Time',
                    'field' => 'delivery_hour',
                    'value' => 'foo',
                ],
            ]
        );

        return $collection->reject(fn($value, $key) => $types && !in_array($value['type'], $types))->all();
    }
}
