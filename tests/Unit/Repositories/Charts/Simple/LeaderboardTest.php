<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Leaderboard;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders\RelatedDataSeeder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class LeaderboardTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class LeaderboardTest extends TestCase
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

        $this->seed(RelatedDataSeeder::class);
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
    public function testGetCountHasMany(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $params = '{
            "type": "Leaderboard",
            "collection": "book",
            "label_field": "label",
            "relationship_field": "comments",
            "limit": 3,
            "aggregate": "Count"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Leaderboard(Book::first());
        $get = $repository->get();

        $result = Book::selectRaw('books.label as key, count(comments.id) as value')
            ->leftJoin('comments', 'books.id', '=', 'comments.book_id')
            ->groupBy('books.label')
            ->limit(3)
            ->orderBy('value', 'desc')
            ->get()->toArray();

        $this->assertIsArray($get);
        $this->assertEquals($result, $get);
    }

    /**
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetCountBelongsToMany(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $params = '{
            "type": "Leaderboard",
            "collection": "book",
            "label_field": "label",
            "relationship_field": "comments",
            "limit": 3,
            "aggregate": "Count"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Leaderboard(Book::first());
        $get = $repository->get();
        $result = Book::selectRaw('books.label as key, count(comments.id) as value')
            ->leftJoin('comments', 'books.id', '=', 'comments.book_id')
            ->groupBy('books.label')
            ->limit(3)
            ->orderBy('value', 'desc')
            ->get()->toArray();

        $this->assertIsArray($get);
        $this->assertEquals($result, $get);
    }

    /**
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetSumHasMany(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $params = '{
            "type": "Leaderboard",
            "collection": "book",
            "label_field": "label",
            "relationship_field": "comments",
            "aggregate_field": "id",
            "limit": 3,
            "aggregate": "Sum"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Leaderboard(Book::first());
        $get = $repository->get();

        $result = Book::selectRaw('books.label as key, sum(comments.id) as value')
            ->leftJoin('comments', 'books.id', '=', 'comments.book_id')
            ->groupBy('books.label')
            ->limit(3)
            ->orderBy('value', 'desc')
            ->get()->toArray();

        $this->assertIsArray($get);
        $this->assertEquals($result, $get);
    }

    /**
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetSumBelongsToMany(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $params = '{
            "type": "Leaderboard",
            "collection": "book",
            "label_field": "label",
            "relationship_field": "ranges",
            "aggregate_field": "id",
            "limit": 3,
            "aggregate": "Sum"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Leaderboard(Book::first());
        $get = $repository->get();

        //select inner join "ranges" on "ranges"."id" = "book_range"."id" group by "books"."label" order by "sum" desc limit 3
        $result = Book::selectRaw('books.label as key, sum(ranges.id) as value')
            ->join('book_range', 'books.id', '=', 'book_range.book_id')
            ->join('ranges', 'book_range.range_id', '=', 'ranges.id')
            ->groupBy('books.label')
            ->limit(3)
            ->orderBy('value', 'desc')
            ->get()
            ->toArray();

        $this->assertIsArray($get);
        $this->assertEquals($result, $get);
    }

    /**
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetException(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $params = '{
            "type": "Leaderboard",
            "collection": "book",
            "label_field": "label",
            "relationship_field": "category",
            "aggregate_field": "id",
            "limit": 3,
            "aggregate": "Sum"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Leaderboard(Book::first());

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Unsupported relation');

        $repository->get();
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $repository = m::mock(Leaderboard::class, [new Book()])
            ->makePartial();
        $data = ['foo' => 10, 'bar' => 20];
        $serialize = $repository->serialize($data);

        $this->assertIsArray($serialize);
        $this->assertEquals([['key' => 'foo', 'value' => 10], ['key' => 'bar', 'value' => 20]], $serialize);
    }
}
