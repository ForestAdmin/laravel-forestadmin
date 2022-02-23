<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
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
