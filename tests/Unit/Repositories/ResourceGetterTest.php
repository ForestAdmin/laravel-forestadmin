<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mockery as m;

/**
 * Class ResourceGetterTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceGetterTest extends TestCase
{
    use FakeData;

    /**
     * @return void
     */
    public function testAll(): void
    {
        $this->getBook()->save();
        $repository = m::mock(ResourceGetter::class, [Book::first(), 'Book'])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Book::first(), $data->items()[0]);
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $repository = m::mock(ResourceGetter::class, [$book, 'Book'])
            ->makePartial();
        $data = $repository->get($book->id);

        $this->assertInstanceOf(Book::class, $data);
        $this->assertEquals(Book::first(), $data);
    }

    /**
     * @return void
     */
    public function testGetExceptionNotFound(): void
    {
        $this->getBook()->save();
        $repository = m::mock(ResourceGetter::class, [new Book(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 Collection not found");

        $repository->get(9999);
    }

    /**
     * @return void
     */
    public function testCount(): void
    {
        $this->getBook()->save();
        $repository = m::mock(ResourceGetter::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->assertEquals(1, $repository->count());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testQuery(): void
    {
        $this->getRequest();
        $repository = new ResourceGetter($this->getBook(), 'Book');
        $query = $this->invokeMethod($repository, 'query');

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
        $repository = m::mock(ResourceGetter::class, [$model, 'Book'])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $fields = $repository->handleFields($model, request()->query('fields')['dummy']);

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
        $repository = m::mock(ResourceGetter::class, [$model, 'Book'])
            ->makePartial();

        $fields = $repository->handleFields($model, null);

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
     * @return mixed
     */
    public function makeHandleWith()
    {
        $this->getRequest();
        $model = $this->getBook();
        $repository = m::mock(ResourceGetter::class, [$model, 'Book'])
            ->makePartial();

        $handleWith = $repository->handleWith($model, request()->query('fields'));

        return $handleWith;
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
}
