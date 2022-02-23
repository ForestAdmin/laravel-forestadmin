<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Value;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
    use FakeData;
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
        $this->getBook()->save();
        $book = Book::first();

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
        $this->assertEquals(['countCurrent' => $book->amount, 'countPrevious' => null], $get);
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

        Book::create(
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

        Book::create(
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
        $this->assertEquals(['countCurrent' => Book::first()->amount, 'countPrevious' => Book::all()->last()->amount], $get);
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
