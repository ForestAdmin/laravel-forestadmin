<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Value;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
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

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testApplyDateFiltersOnPreviousPeriodTodayOperator(): void
    {
        $repository = m::mock(Value::class, [new Book()])
            ->makePartial();
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->invokeProperty($repository, 'timezone', $timezone);
        $interval = $repository->applyDateFiltersOnPreviousPeriod('today');

        $this->assertIsArray($interval);
        $this->assertEquals(Carbon::now($timezone)->subDay()->startOfDay(), $interval[0]);
        $this->assertEquals(Carbon::now($timezone)->subDay()->endOfDay(), $interval[1]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testApplyDateFiltersOnPreviousPeriodPreviousDaysOperator(): void
    {
        $value = 5;
        $repository = m::mock(Value::class, [new Book()])
            ->makePartial();
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->invokeProperty($repository, 'timezone', $timezone);
        $interval = $repository->applyDateFiltersOnPreviousPeriod('previous_x_days', $value);

        $this->assertIsArray($interval);
        $this->assertEquals(Carbon::now($timezone)->subDays($value * 2)->startOfDay(), $interval[0]);
        $this->assertEquals(Carbon::now($timezone)->subDays(2)->endOfDay(), $interval[1]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testApplyDateFiltersOnPreviousPeriodPreviousDaysToDateOperator(): void
    {
        $value = 5;
        $repository = m::mock(Value::class, [new Book()])
            ->makePartial();
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->invokeProperty($repository, 'timezone', $timezone);
        $interval = $repository->applyDateFiltersOnPreviousPeriod('previous_x_days_to_date', $value);

        $this->assertIsArray($interval);
        $this->assertEquals(Carbon::now($timezone)->subDays($value * 2)->startOfDay(), $interval[0]);
        $this->assertEquals(Carbon::now($timezone)->subDay()->endOfDay(), $interval[1]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testApplyDateFiltersOnPreviousPeriodOtherOperators(): void
    {
        $repository = m::mock(Value::class, [new Book()])
            ->makePartial();
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->invokeProperty($repository, 'timezone', $timezone);

        $operators = ['yesterday',
                      'previous_week',
                      'previous_month',
                      'previous_quarter',
                      'previous_year',
                      'previous_week_to_date',
                      'previous_month_to_date',
                      'previous_quarter_to_date',
                      'previous_year_to_date',
        ];
        foreach ($operators as $operator) {
            $period = $operator === 'yesterday' ? 'Day' : Str::ucfirst(Str::of($operator)->explode('_')->get(1));
            $sub = 'sub' . $period . 's';
            $start = 'startOf' . $period;
            $end = 'endOf' . $period;
            $interval = $repository->applyDateFiltersOnPreviousPeriod($operator);

            $this->assertIsArray($interval);
            $this->assertEquals(Carbon::now($timezone)->$sub(2)->$start(), $interval[0]);
            if (Str::endsWith($operator, 'to_date')) {
                $this->assertEquals(Carbon::now($timezone)->$sub()->format('Y-m-d H:i:s'), $interval[1]->format('Y-m-d H:i:s'));
            } else {
                $this->assertEquals(Carbon::now($timezone)->$sub(2)->$end(), $interval[1]);
            }
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testApplyDateFiltersOnPreviousPeriodUnknownOperator(): void
    {
        $repository = m::mock(Value::class, [new Book()])
            ->makePartial();
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->invokeProperty($repository, 'timezone', $timezone);
        $interval = $repository->applyDateFiltersOnPreviousPeriod('foo');

        $this->assertIsArray($interval);
        $this->assertEmpty($interval);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAppendPreviousValue(): void
    {
        $field = 'created_at';
        $operator = 'yesterday';
        $params = [
            'type'            => 'Value',
            'collection'      => 'Book',
            'aggregate'       => 'Sum',
            'aggregate_field' => 'amount',
            'filters'         => '{"aggregator":"and","conditions":[{"field":"' . $field . '","operator":"' . $operator . '","value":null}]}',
        ];

        $request = Request::create('/stats/book', 'POST', $params);
        app()->instance('request', $request);

        $repository = new Value(new Book());
        $appendPreviousPeriod = $this->invokeMethod($repository, 'appendPreviousPeriod');
        $result = [
            'apply'      => true,
            'filter'     => ['field' => 'created_at', 'operator' => 'yesterday', 'value' => null],
            'aggregator' => 'and',
        ];

        $this->assertIsArray($appendPreviousPeriod);
        $this->assertEquals($result, $appendPreviousPeriod);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDontAppendPreviousValue(): void
    {
        $params = [
            'type'            => 'Value',
            'collection'      => 'Book',
            'aggregate'       => 'Sum',
            'aggregate_field' => 'amount',
        ];

        $request = Request::create('/stats/book', 'POST', $params);
        app()->instance('request', $request);

        $repository = new Value(new Book());
        $appendPreviousPeriod = $this->invokeMethod($repository, 'appendPreviousPeriod');
        $result = [
            'apply'      => false,
            'filter'     => null,
            'aggregator' => 'and',
        ];

        $this->assertIsArray($appendPreviousPeriod);
        $this->assertEquals($result, $appendPreviousPeriod);
    }
}
