<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Pie;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class PieTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class PieTest extends TestCase
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
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetCount(): void
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::first();

        $result = Book::select(DB::raw('categories.label as key, COUNT(*) as value'))
            ->join('categories', 'books.category_id', '=', 'categories.id')
            ->groupBy('categories.label')
            ->get()
            ->toArray();

        $params = '{
            "type": "Pie",
            "collection": "Book",
            "group_by_field": "category:label",
            "aggregate": "Count"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);

        $repository = new Pie($book);
        $get = $repository->get();

        $this->assertIsArray($get);
        $this->assertEquals($result, $get);
    }

    /**
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetSum(): void
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::first();
        $result = Book::select(DB::raw('categories.label as key, SUM(books.id) as value'))
            ->join('categories', 'books.category_id', '=', 'categories.id')
            ->groupBy('categories.label')
            ->get()
            ->toArray();

        $params = '{
            "type": "Pie",
            "collection": "Book",
            "group_by_field": "category:label",
            "aggregate": "Sum",
            "aggregate_field": "id"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);

        $repository = new Pie($book);
        $get = $repository->get();

        $this->assertIsArray($get);
        $this->assertEquals($result, $get);
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $repository = m::mock(Pie::class, [new Book()])
            ->makePartial();
        $data = ['foo' => 10, 'bar' => 20];
        $serialize = $repository->serialize($data);

        $this->assertIsArray($serialize);
        $this->assertEquals([['key' => 'foo', 'value' => 10], ['key' => 'bar', 'value' => 20]], $serialize);
    }
}
