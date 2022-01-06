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
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Model;
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
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleFields(): void
    {
        $data = ['fields' => ['model' => 'foo,bar']];
        $model = $this->getLaravelModel();
        $queryBuilder = m::mock(QueryBuilder::class, [$model, $data])
            ->makePartial();

        $fields = $queryBuilder->handleFields($model, $data['fields']['model']);

        $this->assertContains('dummy_tables.id', $fields);
        $this->assertContains('dummy_tables.foo', $fields);
        $this->assertContains('dummy_tables.bar', $fields);
        $this->assertEquals(3, count($fields));
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

        $this->assertEquals(['id', 'foo', 'bar'], array_keys($columns));
        $this->assertEquals(Type::getType('bigint'), $columns['id']->getType());
        $this->assertEquals(Type::getType('string'), $columns['foo']->getType());
        $this->assertEquals(Type::getType('string'), $columns['bar']->getType());
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
                    'id'  => new Column('id', Type::getType('bigint')),
                    'foo' => new Column('foo', Type::getType('string')),
                    'bar' => new Column('bar', Type::getType('string')),
                ]
            );

        $connection = $this->prophesize(Connection::class);
        $connection->getTablePrefix()
            ->shouldBeCalled()
            ->willReturn('prefix.');
        $connection->getDoctrineSchemaManager()
            ->willReturn($schemaManager->reveal());

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
