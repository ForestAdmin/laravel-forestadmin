<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class ResourcesControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourcesControllerTest extends TestCase
{
    use FakeData;
    use FakeSchema;

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('forest.models_namespace', 'ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\\');
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndex(): void
    {
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label']];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($book->id, $data['data'][0]['id']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testShow(): void
    {
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label']];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book/1?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data']['type']);
        $this->assertEquals($book->id, $data['data']['id']);
        $this->assertEquals($book->label, $data['data']['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testShowException(): void
    {
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->get('/forest/book/9999?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(404, $call->getStatusCode());
        $this->assertEquals("🌳🌳🌳 Collection not found", $data['error']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testStore(): void
    {
        $this->getBook()->save();
        $params = [
            'data' => [
                'attributes'    => [
                    'label'      => 'test label',
                    'comment'    => 'test comment',
                    'difficulty' => 'easy',
                    'amount'     => 10,
                    'active'     => true,
                    'options'    => ['key' => 'value'],
                    'other'      => 'N/A',
                ],
                'relationships' => [
                    'category' => [
                        'data' => [
                            'type' => 'categories',
                            'id'   => '1',
                        ],
                    ],
                ],
            ],
            'type' => 'books',
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->post('/forest/book', $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::all()->last();
        $attributes = $data['data']['attributes'];

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(201, $call->getStatusCode());
        $this->assertEquals('book', $data['data']['type']);
        $this->assertEquals($book->id, $data['data']['id']);
        $this->assertEquals($book->label, $attributes['label']);
        $this->assertEquals($book->comment, $attributes['comment']);
        $this->assertEquals($book->difficulty, $attributes['difficulty']);
        $this->assertEquals($book->amount, $attributes['amount']);
        $this->assertEquals($book->active, $attributes['active']);
        $this->assertEquals($book->options, $attributes['options']);
        $this->assertEquals($book->other, $attributes['other']);
        $this->assertEquals($book->category_id, $attributes['category_id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testStoreException(): void
    {
        $this->getBook()->save();
        $params = [
            'data' => [
                'attributes' => [
                    'label' => 'test label',
                ],
            ],
            'type' => 'books',
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->post('/forest/book', $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(422, $call->getStatusCode());
        $this->assertTrue(Str::of($data['error'])->startsWith("🌳🌳🌳 Record create error"));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testUpdate(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $params = [
            'data' => [
                'id'            => $book->id,
                'attributes'    => [
                    'label'      => 'test label',
                    'comment'    => 'test comment',
                    'difficulty' => 'easy',
                    'amount'     => 10,
                    'active'     => true,
                    'options'    => ['key' => 'value'],
                    'other'      => 'N/A',
                ],
                'relationships' => [
                    'category' => [
                        'data' => [
                            'type' => 'categories',
                            'id'   => '1',
                        ],
                    ],
                ],
            ],
            'type' => 'books',
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->put('/forest/book/' . $book->id, $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $attributes = $data['data']['attributes'];
        $paramsAttributes = $params['data']['attributes'];

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(200, $call->getStatusCode());
        $this->assertEquals('book', $data['data']['type']);
        $this->assertEquals($paramsAttributes['label'], $attributes['label']);
        $this->assertEquals($paramsAttributes['comment'], $attributes['comment']);
        $this->assertEquals($paramsAttributes['difficulty'], $attributes['difficulty']);
        $this->assertEquals($paramsAttributes['amount'], $attributes['amount']);
        $this->assertEquals($paramsAttributes['active'], $attributes['active']);
        $this->assertEquals($paramsAttributes['options'], $attributes['options']);
        $this->assertEquals($paramsAttributes['other'], $attributes['other']);
        $this->assertEquals($params['data']['relationships']['category']['data']['id'], $attributes['category_id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testUpdateException(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $params = [
            'data' => [
                'id'         => $book->id,
                'attributes' => [
                    'foo' => 'bar',
                ],
            ],
            'type' => 'books',
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->put('/forest/book/' . $book->id, $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(422, $call->getStatusCode());
        $this->assertTrue(Str::of($data['error'])->startsWith("🌳🌳🌳 Record update error"));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroy(): void
    {
        $this->getBook()->save();
        $book = Book::first();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->delete('/forest/book/' . $book->id);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(204, $call->getStatusCode());
        $this->assertNull(Book::find($book->id));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyException(): void
    {
        $this->getBook()->save();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->delete('/forest/book/9999');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(404, $call->getStatusCode());
        $this->assertEquals("🌳🌳🌳 Record destroy error: Collection nof found", $data['error']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testCount(): void
    {
        $book = $this->getBook();
        $call = $this->get('/forest/book/count');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($book->count(), $data['count']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyBulk(): void
    {
        for ($i = 0; $i < 19; $i++) {
            $this->getBook()->save();
        }
        $params = [
            'data' => [
                'attributes' => [
                    'ids'                      => ['1', '2', '3', '4', '5'],
                    'collection_name'          => 'book',
                    'parent_collection_name'   => null,
                    'parent_collection_id'     => null,
                    'parent_association_name'  => null,
                    'all_records'              => true,
                    'all_records_subset_query' => [
                        'fields[book]'     => 'id,label,comment,difficulty,amount,active,options,other,created_at,updated_at,category,editor,image',
                        'fields[category]' => 'label',
                        'fields[editor]'   => 'name',
                        'fields[image]'    => 'name',
                        'page[number]'     => 1,
                        'page[size]'       => 15,
                        'sort'             => '-id',
                        'searchExtended'   => 0,
                    ],
                    'all_records_ids_excluded' => [
                        '6', '7', '8', '9', '10', '12', '13', '14', '15', '16', '17', '18', '19', '20',
                    ],
                    'smart_action_id'          => null,
                ],
                'type'       => 'action-requests',
            ],
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->delete('/forest/book/', $params);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(204, $call->getStatusCode());
        $this->assertTrue(empty(Book::where('id', '<=', 5)->get()->toArray()));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyBulkException(): void
    {
        $params = [
            'data' => [
                'attributes' => [
                    'ids'                      => ['1', '2', '3', '4', '5'],
                    'collection_name'          => 'book',
                    'parent_collection_name'   => null,
                    'parent_collection_id'     => null,
                    'parent_association_name'  => null,
                    'all_records'              => true,
                    'all_records_subset_query' => [
                        'fields[book]'     => 'id,label,comment,difficulty,amount,active,options,other,created_at,updated_at,category,editor,image',
                        'fields[category]' => 'label',
                        'fields[editor]'   => 'name',
                        'fields[image]'    => 'name',
                        'page[number]'     => 1,
                        'page[size]'       => 15,
                        'sort'             => '-id',
                        'searchExtended'   => 0,
                    ],
                    'all_records_ids_excluded' => [
                        '6', '7', '8', '9', '10', '12', '13', '14', '15', '16', '17', '18', '19', '20',
                    ],
                    'smart_action_id'          => null,
                ],
                'type'       => 'action-requests',
            ],
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->delete('/forest/book/', $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(404, $call->getStatusCode());
        $this->assertEquals("🌳🌳🌳 Records destroy error: Collection nof found", $data['error']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSearchWithQueryBuilder(): void
    {
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label'], 'search' => '1'];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($book->id, $data['data'][0]['id']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSearchExtendedWithQueryBuilder(): void
    {
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label'], 'search' => 'bar', 'searchExtended' => 1];
        App::shouldReceive('basePath')->andReturn(null);
        $fakeCategorySchema = [
            "collections" => [
                [
                    "name"   => "category",
                    "fields" => [
                        [
                            "field"         => "label",
                            "type"          => "String",
                            "default_value" => null,
                            "enums"         => null,
                            "integration"   => null,
                            "is_filterable" => true,
                            "is_read_only"  => false,
                            "is_required"   => false,
                            "is_sortable"   => true,
                            "is_virtual"    => false,
                            "is_searchable" => null,
                            "reference"     => null,
                            "inverse_of"    => null,
                            "widget"        => null,
                            "validations"   => [],
                        ],
                    ],
                ]
            ]
        ];
        File::shouldReceive('get')->andReturn(json_encode($fakeCategorySchema, JSON_THROW_ON_ERROR));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($book->id, $data['data'][0]['id']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSearchWithQueryBuilderNoResult(): void
    {
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label'], 'search' => '9999'];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEmpty($data['data']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSortAscWithQueryBuilder(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
        }
        $params = ['fields' => ['book' => 'id,label'], 'sort' => 'id'];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals(Book::first()->id, $data['data'][0]['id']);
        $this->assertEquals(Book::all()->last()->id, $data['data'][1]['id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSortDescWithQueryBuilder(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
        }
        $params = ['fields' => ['book' => 'id,label'], 'sort' => '-id'];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals(Book::all()->last()->id, $data['data'][0]['id']);
        $this->assertEquals(Book::first()->id, $data['data'][1]['id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testFiltersWithQueryBuilder(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
        }
        $book = Book::first();
        $book->label = 'my favorite book';
        $book->save();

        $params = ['fields' => ['book' => 'id,label'], 'filters' => '{"field":"label","operator":"equal","value":"my favorite book"}'];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertCount(1, $data['data']);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals('my favorite book', $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testFiltersAggregatorWithQueryBuilder(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
        }
        $book = Book::first();
        $book->difficulty = 'hard';
        $book->save();

        $params = [
            'fields' => ['book' => 'id,label,difficulty'],
            'filters' => '{"aggregator":"and","conditions":[{"field":"label","operator":"equal","value":"foo"},{"field":"difficulty","operator":"equal","value":"hard"}]}'
        ];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertCount(1, $data['data']);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals('hard', $data['data'][0]['attributes']['difficulty']);
    }
}
