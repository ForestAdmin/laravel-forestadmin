<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use ForestAdmin\LaravelForestAdmin\Services\QueryBuilder;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Mockery as m;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class QueryBuilderTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class QueryBuilderTest extends TestCase
{
    use ProphecyTrait;
    use FakeData;

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     * @throws \ReflectionException
     */
    public function testBuild(): void
    {
        $data = ['key' => 'value'];
        $model = $this->getLaravelModel();
        $queryBuilder = m::mock(QueryBuilder::class, [$model, $data])
            ->makePartial();

        $entity = $this->invokeProperty($queryBuilder, 'model');
        $table = $this->invokeProperty($queryBuilder, 'table');
        $database = $this->invokeProperty($queryBuilder, 'database');
        $params = $this->invokeProperty($queryBuilder, 'params');

        $this->assertEquals($model, $entity);
        $this->assertEquals('dummy_tables', $table);
        $this->assertEquals('prefix', $database);
        $this->assertEquals($data, $params);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testOf(): void
    {
        $data = ['fields' => ['model' => 'foo,bar']];
        $queryBuilder = QueryBuilder::of(new Book(), $data);
        $this->assertInstanceOf(EloquentQueryBuilder::class, $queryBuilder);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testIsUuid(): void
    {
        $data = [];
        $model = $this->getLaravelModel();
        $queryBuilder = m::mock(QueryBuilder::class, [$model, $data])
            ->makePartial();
        $stringValue = $queryBuilder->isUuid('foo');
        $uuidValue = $queryBuilder->isUuid('AA111111-AAAA-1111-1111-11AA11AA11AA');

        $this->assertFalse($stringValue);
        $this->assertTrue($uuidValue);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleFields(): void
    {
        $data = ['fields' => ['model' => 'foo,bar']];
        $model = $this->getLaravelModel();
        $queryBuilder = m::mock(QueryBuilder::class, [$model, $data])->makePartial();

        $fields = $queryBuilder->handleFields($model, $data['fields']['model']);

        $this->assertContains('dummy_tables.id', $fields);
        $this->assertContains('dummy_tables.foo', $fields);
        $this->assertContains('dummy_tables.bar', $fields);
        $this->assertCount(3, $fields);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleFieldsWithoutQueryFields(): void
    {
        $model = $this->getLaravelModel();
        $queryBuilder = m::mock(QueryBuilder::class, [$model, []])
            ->makePartial();

        $fields = $queryBuilder->handleFields($model);

        $this->assertEquals(['dummy_tables.*'], $fields);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testGetColumns(): void
    {
        $model = $this->getLaravelModel();
        $queryBuilder = m::mock(QueryBuilder::class, [$model, []])
            ->makePartial();
        $columns = $queryBuilder->getColumns($model);

        $this->assertEquals(['id', 'foo', 'bar', 'uuid'], array_keys($columns));
        $this->assertEquals(Type::getType('bigint'), $columns['id']->getType());
        $this->assertEquals(Type::getType('string'), $columns['foo']->getType());
        $this->assertEquals(Type::getType('string'), $columns['bar']->getType());
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleSearchFieldOnNumber(): void
    {
        $field = 'id';
        $value = '1';
        $handleSearchInt = $this->handleSearchField($field, 'Number', $value);
        $expectResult = [
            'type'     => 'Basic',
            'column'   => 'dummy_tables.' . $field,
            'operator' => '=',
            'value'    => (int) $value,
            'boolean'  => 'or',
        ];

        $this->assertEquals($expectResult, $handleSearchInt->getQuery()->wheres[0]);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleSearchFieldOnEnum(): void
    {
        $field = 'foo';
        $value = 'TEST';
        $handleSearchEnum = $this->handleSearchField($field, 'Enum', $value);
        $expectResult = [
            'type'     => 'Basic',
            'column'   => 'dummy_tables.' . $field,
            'operator' => '=',
            'value'    => $value,
            'boolean'  => 'or',
        ];

        $this->assertEquals($expectResult, $handleSearchEnum->getQuery()->wheres[0]);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleSearchFieldOnUuid(): void
    {
        $field = 'uuid';
        $value = 'AA111111-AAAA-1111-1111-11AA11AA11AA';
        $handleSearchUuid = $this->handleSearchField($field, 'String', $value);
        $expectResult = [
            'type'     => 'Basic',
            'column'   => 'dummy_tables.' . $field,
            'operator' => '=',
            'value'    => $value,
            'boolean'  => 'or',
        ];

        $this->assertEquals($expectResult, $handleSearchUuid->getQuery()->wheres[0]);
    }


    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleSearchFieldOnString(): void
    {
        $field = 'bar';
        $value = 'Test value';
        $handleSearchString = $this->handleSearchField($field, 'String', $value);
        $expectResult = [
            'type'    => 'raw',
            'sql'     => 'LOWER (dummy_tables.bar) LIKE LOWER(?)',
            'boolean' => 'or',
        ];

        $this->assertEquals($expectResult, $handleSearchString->getQuery()->wheres[0]);
    }

    /**
     * @param $field
     * @param $fieldType
     * @param $value
     * @return mixed
     * @throws Exception
     * @throws SchemaException
     */
    public function handleSearchField($field, $fieldType, $value)
    {
        $model = $this->getLaravelModel();
        $field = ['field' => $field, 'type' => $fieldType];
        $data = ['search' => $value];
        $queryBuilder = m::mock(QueryBuilder::class, [$model, $data])->makePartial();
        $builder = new EloquentQueryBuilder(new Builder($model->getConnection()));
        return $queryBuilder->handleSearchField($builder, $model, $field, $value);
    }

    /**
     * @return object
     * @throws Exception
     * @throws SchemaException
     */
    public function getLaravelModel()
    {
        $schemaManager = $this->prophesize(AbstractSchemaManager::class);
        $schemaManager->listTableColumns(Argument::any(), Argument::any())
            ->willReturn(
                [
                    'id'   => new Column('id', Type::getType('bigint')),
                    'foo'  => new Column('foo', Type::getType('string')),
                    'bar'  => new Column('bar', Type::getType('string')),
                    'uuid' => new Column('foo', Type::getType('string')),
                ]
            );

        $connection = $this->prophesize(Connection::class);
        $connection->getTablePrefix()
            ->shouldBeCalled()
            ->willReturn('prefix.');
        $connection->getDoctrineSchemaManager()
            ->willReturn($schemaManager->reveal());
        $connection->getQueryGrammar()
            ->willReturn(null);
        $connection->getPostProcessor()
            ->willReturn(null);

        $model = $this->prophesize(Model::class);
        $model
            ->getConnection()
            ->shouldBeCalled()
            ->willReturn($connection->reveal());
        $model
            ->getTable()
            ->shouldBeCalled()
            ->willReturn('dummy_tables');
        $model
            ->getKeyName()
            ->willReturn('id');

        return $model->reveal();
    }
}
