<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Value;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class ValueTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ValueTest extends TestCase
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
    public function testGet(): void
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::first();
        $result = Book::sum('amount');

        $params = '{
            "type": "Value",
            "collection": "Book",
            "aggregate": "Sum",
            "aggregate_field": "amount"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);

        $repository = new Value($book);
        $get = $repository->get();

        $this->assertIsArray($get);
        $this->assertEquals(['countCurrent' => $result, 'countPrevious' => null], $get);
    }

    /**
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    public function testGetWithPreviousPeriod(): void
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $book1 = Book::create(
            [
                'label'        => 'foo bar',
                'comment'      => '',
                'difficulty'   => 'easy',
                'amount'       => 1000,
                'options'      => [],
                'category_id'  => 1,
                'published_at' => Carbon::now()->subDays(),
            ]
        );

        $book2 = Book::create(
            [
                'label'        => 'foo bar',
                'comment'      => '',
                'difficulty'   => 'easy',
                'amount'       => 2000,
                'options'      => [],
                'category_id'  => 1,
                'published_at' => Carbon::now()->subDays(2),
            ]
        );

        $params = '{
            "type": "Value",
            "collection": "Book",
            "aggregate": "Sum",
            "aggregate_field": "amount",
            "filters": "{\"aggregator\":\"and\",\"conditions\":[{\"field\":\"published_at\",\"operator\":\"yesterday\",\"value\":null}]}"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);

        $repository = new Value(Book::first());
        $get = $repository->get();

        $this->assertIsArray($get);
        $this->assertEquals(['countCurrent' => $book1->amount, 'countPrevious' => $book2->amount], $get);
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $repository = m::mock(Value::class, [new Book()])
            ->makePartial();
        $data = [10, 100];
        $serialize = $repository->serialize($data);

        $this->assertIsArray($serialize);
        $this->assertEquals(['countCurrent' => $data[0], 'countPrevious' => $data[1]], $serialize);
    }
}
