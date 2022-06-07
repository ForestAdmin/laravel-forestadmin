<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
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
    use ScopeManagerFactory;
    use FakeSchema;

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
        $repository = m::mock(ResourceGetter::class, [Book::first()])
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
        $book = Book::first();
        $repository = m::mock(ResourceGetter::class, [$book])
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
        $repository = m::mock(ResourceGetter::class, [new Book()])
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
        $repository = m::mock(ResourceGetter::class, [Book::first()])
            ->makePartial();

        $this->assertEquals(Book::count(), $repository->count());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testQuery(): void
    {
        $this->getRequest();
        $repository = new ResourceGetter(Book::first());
        $query = $this->invokeMethod($repository, 'query');

        $this->assertInstanceOf(Builder::class, $query);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testAllWithSegmentException(): void
    {
        $this->getRequest(true);
        $repository = m::mock(ResourceGetter::class, [Book::first()])
            ->makePartial();
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 There is no smart-segment foo");

        $repository->all();
    }

    /**
     * @param bool $withSegment
     * @return void
     */
    public function getRequest($withSegment = false): void
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

        if ($withSegment) {
            $params['segment'] = 'foo';
        }

        $request = Request::create('/', 'GET', $params);
        app()->instance('request', $request);
    }
}
