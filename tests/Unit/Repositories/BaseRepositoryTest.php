<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\BaseRepository;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class ForestModelTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BaseRepositoryTest extends TestCase
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
        $model = $this->getLaravelModel();
        $baseRepository = m::mock(BaseRepository::class, [$model])
            ->makePartial();

        $table = $this->invokeProperty($baseRepository, 'table');
        $database = $this->invokeProperty($baseRepository, 'database');

        $this->assertEquals('dummy_tables', $table);
        $this->assertEquals('prefix', $database);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testThrowException(): void
    {
        $model = $this->getLaravelModel();
        $baseRepository = m::mock(BaseRepository::class, [$model])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('test error');

        $this->invokeMethod($baseRepository, 'throwException', ['test error']);
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
                    'id' => new Column('id', Type::getType('bigint')),
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
            ->shouldBeCalledOnce()
            ->willReturn('dummy_tables');

        return $model->reveal();
    }
}
