<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasFilters;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class HasFiltersTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasFiltersTest extends TestCase
{
    use FakeSchema;
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
    public function testValidateValueWillReturnTrue(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();

        $data = [
            ['type' => 'Number', 'value' => 10],
            ['type' => 'Uuid', 'value' => '4c4bd11b-5971-33c8-902b-4e11e79c1a2f'],
            ['type' => 'Date', 'value' => '2022-01-24 12:00:00'],
            ['type' => 'Dateonly', 'value' => '2022-01-24'],
            ['type' => 'String', 'value' => 'foo'],
        ];

        foreach ($data as $value) {
            $isValid = $this->invokeMethod($queryBuilder, 'validateValue', [$value['value'], $value['type']]);
            $this->assertTrue($isValid);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateValueWillReturnFalse(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();

        $data = [
            ['type' => 'Number', 'value' => 'foo'],
            ['type' => 'Uuid', 'value' => 'foo'],
            ['type' => 'Date', 'value' => 'foo'],
            ['type' => 'Dateonly', 'value' => 'foo'],
            ['type' => 'Time', 'value' => 'foo'],
        ];

        foreach ($data as $value) {
            $isValid = $this->invokeMethod($queryBuilder, 'validateValue', [$value['value'], $value['type']]);
            $this->assertFalse($isValid);
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateException(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();
        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Unknown type: UnknownType');
        $this->invokeMethod($queryBuilder, 'validateValue', ['foo', 'UnknownType']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSetAggregator(): void
    {
        $defaultOperator = 'and';
        $orOperator = 'or';
        $trait = $this->getObjectForTrait(HasFilters::class);

        $this->invokeMethod($trait, 'setAggregator', [$defaultOperator]);
        $this->assertEquals($defaultOperator, $this->invokeProperty($trait, 'aggregator'));

        $this->invokeMethod($trait, 'setAggregator', [$orOperator]);
        $this->assertEquals($orOperator, $this->invokeProperty($trait, 'aggregator'));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSetAggregatorException(): void
    {
        $invalidOperator = 'foo';
        $trait = $this->getObjectForTrait(HasFilters::class);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Unsupported operator: foo');
        $this->invokeMethod($trait, 'setAggregator', [$invalidOperator]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testIsOperatorValidToFieldTypeWillReturnTrue(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);

        $data = [
            'Boolean'  => ['equal'],
            'Date'     => ['present'],
            'Dateonly' => ['equal'],
            'Date'     => ['before'],
            'Dateonly' => ['after'],
            'Number'   => ['not_equal'],
            'String'   => ['contains'],
            'Uuid'     => ['blank'],
            'Time'     => ['less_than'],
            'Json'     => ['present'],
        ];

        foreach ($data as $type => $operators) {
            foreach ($operators as $operator) {
                $isValid = $this->invokeMethod($trait, 'isOperatorValidToFieldType', [$type, $operator]);
                $this->assertTrue($isValid);
            }
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testIsOperatorValidToFieldTypeWillReturnFalse(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);

        $data = [
            'Boolean'  => ['less_than'],
            'Date'     => ['in'],
            'Dateonly' => ['in'],
            'Number'   => ['starts_with'],
            'String'   => ['previous_year'],
            'Uuid'     => ['contains'],
            'Time'     => ['ends_with'],
            'Json'     => ['not_equal'],
        ];

        foreach ($data as $type => $operators) {
            foreach ($operators as $operator) {
                $isValid = $this->invokeMethod($trait, 'isOperatorValidToFieldType', [$type, $operator]);
                $this->assertFalse($isValid);
            }
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testIsOperatorValidToFieldTypeException(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Field type unknown: unknownType');
        $this->invokeMethod($trait, 'isOperatorValidToFieldType', ['unknownType', 'less_than']);
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testGetTypeByField(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $model = new Book();
        $data = [
            'id'    => ['books.id', 'Number'],
            'label' => ['books.label', 'String'],
        ];

        foreach ($data as $field => $value) {
            $result = $this->invokeMethod($trait, 'getTypeByField', [$model, $field]);

            $this->assertIsArray($result);
            $this->assertEquals($value, $result);
        }
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testGetTypeByFieldException(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $model = new Book();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Unknown field books.foo for this collection');
        $this->invokeMethod($trait, 'getTypeByField', [$model, 'foo']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testParseFilters(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);
        $json = '{"field":"label","operator":"equal","value":"test"}';
        $expected = [
            'and',
            [
                [
                    'field'    => 'label',
                    'operator' => 'equal',
                    'value'    => 'test',
                ],
            ],
        ];

        $result = $this->invokeMethod($trait, 'parseFilters', [$json]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testParseFiltersWithAggregator(): void
    {
        $trait = $this->getObjectForTrait(HasFilters::class);
        $json = '{"aggregator":"and","conditions":[{"field":"advertisement:id","operator":"equal","value":"test"},{"field":"active","operator":"equal","value":true}]}';
        $expected = [
            'and',
            [
                [
                    'field'    => 'advertisement:id',
                    'operator' => 'equal',
                    'value'    => 'test',
                ],
                [
                    'field'    => 'active',
                    'operator' => 'equal',
                    'value'    => true,
                ],
            ],
        ];

        $result = $this->invokeMethod($trait, 'parseFilters', [$json]);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testHandleFilter(): void
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();
        $queryBuilder->setAggregator('and');

        $data = [
            "field"    => "label",
            "operator" => "equal",
            "value"    => "foo",
        ];

        $query = $queryBuilder->query();
        $this->invokeMethod(
            $queryBuilder,
            'handleFilter',
            [$query, $data]
        );

        $this->assertIsArray($query->getQuery()->wheres);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testHandleFilterRelation(): void
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();
        $queryBuilder->setAggregator('and');

        $data = [
            "field"    => "editor:name",
            "operator" => "equal",
            "value"    => "foo",
        ];

        $query = $queryBuilder->query();
        $this->invokeMethod(
            $queryBuilder,
            'handleFilter',
            [$query, $data]
        );
        $queryNested = $query->getQuery()->wheres;
        $this->assertIsArray($queryNested);
        $this->assertIsArray($queryNested[0]);
        $this->assertArrayHasKey('type', $queryNested[0]);
        $this->assertEquals('Exists', $queryNested[0]['type']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testCallFilter(): void
    {
        $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
            ->makePartial();
        $queryBuilder->setAggregator('and');


        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 The operator previous_week is not allowed to the field type : String');
        $this->invokeMethod(
            $queryBuilder,
            'CallFilter',
            [$queryBuilder->query(), 'label', 'String', 'previous_week', 'foo']
        );
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testCallFilterException(): void
    {
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $timezone = new \DateTimeZone('UTC');
        $data = [
            [
                'type' => 'String',
                'field' => 'label',
                'operator' => 'equal',
                'value' => 'foo',
            ],
            [
                'type' => 'Date',
                'field' => 'published_at',
                'operator' => 'before',
                'value' => '2022-01-01 12:00:00',
            ],
            [
                'type' => 'Dateonly',
                'field' => 'sold_at',
                'operator' => 'previous_week',
                'value' => '',
            ]
        ];

        foreach ($data as $value) {
            $queryBuilder = m::mock(QueryBuilder::class, [new Book(), []])
                ->makePartial();
            $this->invokeProperty($queryBuilder, 'timezone', $timezone);
            $queryBuilder->setAggregator('and');
            $query = $queryBuilder->query();
            $this->invokeMethod(
                $queryBuilder,
                'CallFilter',
                [$query, $value['field'], $value['type'], $value['operator'], $value['value']]
            );
            $this->assertIsArray($query->getQuery()->wheres);
        }
    }
}
