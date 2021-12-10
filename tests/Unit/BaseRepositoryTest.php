<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use ForestAdmin\LaravelForestAdmin\Repositories\BaseRepository;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Mockery as m;

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
     * @return void
     */
    public function testAll(): void
    {
        $this->getBook()->save();
        $baseRepository = m::mock(BaseRepository::class, [Book::first()])
            ->makePartial();
        $data = $baseRepository->all();

        $this->assertIsArray($data);
        $this->assertEquals('Book', $data['data'][0]['type']);
        $this->assertEquals($this->getBook()->id, $data['data'][0]['id']);
        $attributes = $data['data'][0]['attributes'];
        $this->assertEquals($this->getBook()->label, $attributes['label']);
        $this->assertEquals($this->getBook()->comment, $attributes['comment']);
        $this->assertEquals($this->getBook()->difficulty, $attributes['difficulty']);
        $this->assertEquals($this->getBook()->amount, $attributes['amount']);
        $this->assertEquals($this->getBook()->options, $attributes['options']);
        $this->assertEquals($this->getBook()->category_id, $attributes['category_id']);
    }

    /**
     * @return void
     */
    public function testCount(): void
    {
        $this->getBook()->save();
        $baseRepository = m::mock(BaseRepository::class, [Book::first()])
            ->makePartial();

        $this->assertEquals(1, $baseRepository->count());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testQuery(): void
    {
        $this->getRequest();
        $baseRepository = new BaseRepository($this->getBook());
        $query = $this->invokeMethod($baseRepository, 'query');

        $this->assertInstanceOf(Builder::class, $query);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleFields(): void
    {
        $this->getRequest();
        $model = $this->getBook();
        $baseRepository = m::mock(BaseRepository::class, [$model])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $fields = $baseRepository->handleFields($model, request()->query('fields')['dummy']);

        $this->assertEquals(['books.id'], $fields);
    }

    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testHandleFieldsWithoutQueryFields(): void
    {
        $model = $this->getBook();
        $baseRepository = m::mock(BaseRepository::class, [$model])
            ->makePartial();

        $fields = $baseRepository->handleFields($model, null);

        $this->assertEquals(['books.*'], $fields);
    }

    /**
     * @return void
     */
    public function testHandleWithBelongsto(): void
    {
        $values = [
            'fields'         => 'categories.id',
            'foreign_key'    => 'books.category_id',
        ];

        $this->assertEquals($values, $this->makeHandleWith()['category']);
    }

    /**
     * @return void
     */
    public function testHandleWithHasOne(): void
    {
        $values = [
            'fields'         => 'editors.name,editors.id,editors.book_id',
            'foreign_key'    => null,
        ];

        $this->assertEquals($values, $this->makeHandleWith()['editor']);
    }

    /**
     * @return void
     */
    public function testHandleWithMorphOne(): void
    {
        $values = [
            'fields'         => 'images.name,images.id,images.imageable_id,imageable_type',
            'foreign_key'    => null,
        ];

        $this->assertEquals($values, $this->makeHandleWith()['image']);
    }

    /**
     * @return mixed
     */
    public function makeHandleWith()
    {
        $this->getRequest();
        $model = $this->getBook();
        $baseRepository = m::mock(BaseRepository::class, [$model])
            ->makePartial();

        $handleWith = $baseRepository->handleWith($model, request()->query('fields'));

        return $handleWith;
    }

    /**
     * @return Book
     */
    public function getBook()
    {
        $category = new Category();
        $category->id = 1;
        $category->label = 'bar';

        $book = new Book();
        $book->id = 1;
        $book->label = 'foo';
        $book->comment = 'test value';
        $book->difficulty = 'easy';
        $book->amount = 50.20;
        $book->options = [];
        $book->category_id = $category->id;
        $book->setRelation('category', $category);

        return $book;
    }

    /**
     * @return void
     */
    public function getRequest(): void
    {
        $params = [
            'fields' => [
                'dummy'    => 'id,foo',
                'category' => 'id',
                'editor'   => 'name',
                'image'    => 'name',
            ],
            'page'   => [
                'number' => 1,
                'size'   => 15,
            ],
        ];
        $request = Request::create('/', 'GET', $params);
        app()->instance('request', $request);
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
                    'id'            => new Column('id', Type::getType('bigint')),
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
