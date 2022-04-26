<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Line;
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
 * Class LineTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class LineTest extends TestCase
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
        Book::create(
            [
                'label'        => 'foo bar',
                'comment'      => '',
                'difficulty'   => 'easy',
                'amount'       => 1000,
                'options'      => [],
                'category_id'  => 1,
                'published_at' => Carbon::now(),
            ]
        );

        $params = '{
            "type": "Line",
            "collection": "Book",
            "group_by_date_field": "published_at",
            "aggregate": "Count",
            "time_range": "Day"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Line(Book::first());
        $get = $repository->get();

        $this->assertIsArray($get);
        $this->assertEquals([['label' => Carbon::now()->format('d/m/Y'), 'values' => ['value' => '1']]], $get);
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
        Book::create(
            [
                'label'        => 'foo bar',
                'comment'      => '',
                'difficulty'   => 'easy',
                'amount'       => 1000,
                'options'      => [],
                'category_id'  => 1,
                'published_at' => Carbon::now(),
            ]
        );

        $params = '{
            "type": "Line",
            "collection": "Book",
            "group_by_date_field": "published_at",
            "aggregate": "Sum",
            "aggregate_field": "amount",
            "time_range": "Day"
        }';

        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);
        $repository = new Line(Book::first());
        $get = $repository->get();

        $this->assertIsArray($get);
        $this->assertEquals([['label' => Carbon::now()->format('d/m/Y'), 'values' => ['value' => '1000.0']]], $get);
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $repository = m::mock(Line::class, [new Book()])
            ->makePartial();
        $data = [
            'W51-2021' => 10,
            'W01-2022' => 20,
            'W02-2022' => 30,
            'W03-2022' => 40,
            'W04-2022' => 50,
        ];
        $serialize = $repository->serialize($data);
        $result = [
            [
                'label'  => 'W51-2021',
                'values' => [
                    'value' => 10,
                ],
            ],
            [
                'label'  => 'W01-2022',
                'values' => [
                    'value' => 20,
                ],
            ],
            [
                'label'  => 'W02-2022',
                'values' => [
                    'value' => 30,
                ],
            ],
            [
                'label'  => 'W03-2022',
                'values' => [
                    'value' => 40,
                ],
            ],
            [
                'label'  => 'W04-2022',
                'values' => [
                    'value' => 50,
                ],
            ],
        ];

        $this->assertIsArray($serialize);
        $this->assertEquals($result, $serialize);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetFormat(): void
    {
        $types = [
            'day'   => 'd/m/Y',
            'week'  => '\WW-Y',
            'month' => 'M Y',
            'year'  => 'Y',
            'foo'   => '',
        ];

        foreach ($types as $type => $result) {
            $params = '{   
                "type": "Line",
                "collection": "Book",
                "group_by_date_field": "created_at",
                "aggregate": "Count",
                "time_range": "' . $type . '"
            }';
            $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
            app()->instance('request', $request);

            $repository = new Line(new Book());
            $format = $repository->getFormat();

            $this->assertEquals($result, $format);
        }
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetFormatException(): void
    {
        $params = '{   
            "type": "Line",
            "collection": "Book",
            "group_by_date_field": "created_at",
            "aggregate": "Count"
        }';
        $request = Request::create('/stats/book', 'POST', json_decode($params, true, 512, JSON_THROW_ON_ERROR));
        app()->instance('request', $request);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The parameter time_range is not defined");

        $repository = new Line(new Book());
        $repository->getFormat();
    }
}
