<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Exports\CollectionExport;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockIpWhitelist;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResourcesControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourcesControllerTest extends TestCase
{
    use FakeSchema;
    use MockForestUserFactory;
    use ScopeManagerFactory;
    use MockIpWhitelist;

    /**
     * @var ForestUser
     */
    private ForestUser $forestUser;

    /**
     * @return void
     * @throws \JsonException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->forestUser = new ForestUser(
            [
                'id'               => 1,
                'email'            => 'john.doe@forestadmin.com',
                'first_name'       => 'John',
                'last_name'        => 'Doe',
                'rendering_id'     => 1,
                'tags'             => [],
                'teams'            => 'Operations',
                'exp'              => 1643825269,
                'permission_level' => 'admin',
            ]
        );

        $forestResourceOwner = new ForestResourceOwner(
            array_merge(
                [
                    'type'                              => 'users',
                    'two_factor_authentication_enabled' => false,
                    'two_factor_authentication_active'  => false,
                ],
                $this->forestUser->getAttributes()
            ),
            $this->forestUser->getAttribute('rendering_id')
        );

        $this->withHeader('Authorization', 'Bearer ' . $forestResourceOwner->makeJwt());
        $this->mockForestUserFactory();
        $this->mockIpWhitelist();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndex(): void
    {
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['book' => 'id,label']];
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
    public function testIndexWithScope(): void
    {
        $this->makeScopeManager($this->forestUser, $this->getScopesFromApi());
        $book = Book::first();
        $book->label = 'foo';
        $book->difficulty = 'hard';
        $book->save();
        $params = ['fields' => ['book' => 'id,label,difficulty']];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedCount = Book::where(['difficulty' => 'hard', 'label' => 'foo'])->count();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertCount($expectedCount, $data['data']);
        $this->assertEquals($book->difficulty, $data['data'][0]['attributes']['difficulty']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndexWithSegment(): void
    {
        $this->makeScopeManager($this->forestUser);
        Category::truncate();
        for ($i = 1; $i < 5; $i++) {
            Category::create(['label' => 'Foo' . $i]);
        }
        $params = ['fields' => ['category' => 'id,label'], 'segment' => 'bestName'];
        $call = $this->get('/forest/category?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('category', $data['data'][0]['type']);
        $this->assertCount(2, $data['data']);
        $this->assertEquals('Foo1', $data['data'][0]['attributes']['label']);
        $this->assertEquals('Foo2', $data['data'][1]['attributes']['label']);
    }


    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndexWithSegmentDoesNotExist(): void
    {
        $this->withoutExceptionHandling();
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['category' => 'id,label'], 'segment' => 'foo'];

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 There is no smart-segment foo");
        $this->get('/forest/category?' . http_build_query($params));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndexPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->getJson('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndexUserNotLoggedIn(): void
    {
        $this->withHeader('Authorization', '');
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->getJson('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('You must be logged in to access at this resource.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testExport(): void
    {
        $this->makeScopeManager($this->forestUser);
        $params = [
            'fields'   => ['book' => 'id,label'],
            'filename' => 'books',
            'header'   => 'id,label',
        ];

        $call = $this->get('/forest/book.csv?' . http_build_query($params));
        $data = str_getcsv($call->getContent(), "\n");
        $book = Book::select('id', 'label')->first();

        $this->assertInstanceOf(\Illuminate\Http\Response::class, $call->baseResponse);
        $this->assertEquals($params['header'], $data[0]);
        $this->assertEquals(implode(',', $book->toArray()), str_replace('"', '', $data[1]));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testExportPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
        $params = [
            'fields'   => ['book' => 'id,label'],
            'filename' => 'books',
            'header'   => 'id,label',
        ];
        $call = $this->getJson('/forest/book.csv?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testShow(): void
    {
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['book' => 'id,label']];
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
    public function testShowPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->getJson('/forest/book/1?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testShowException(): void
    {
        $this->makeScopeManager($this->forestUser);
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
        $this->makeScopeManager($this->forestUser);
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
    public function testStorePermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
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
        $call = $this->postJson('/forest/book', $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testStoreException(): void
    {
        $this->makeScopeManager($this->forestUser);
        $params = [
            'data' => [
                'attributes' => [
                    'label' => 'test label',
                ],
            ],
            'type' => 'books',
        ];
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
        $this->makeScopeManager($this->forestUser);
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
    public function testUpdateSmartField(): void
    {
        $this->makeScopeManager($this->forestUser);
        $book = Book::first();
        $params = [
            'data' => [
                'id'         => $book->id,
                'attributes' => [
                    'reference' => 'new label-hard',
                ],
            ],
            'type' => 'books',
        ];
        $call = $this->put('/forest/book/' . $book->id, $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $attributes = $data['data']['attributes'];

        $this->assertEquals('new label', $attributes['label']);
        $this->assertEquals('hard', $attributes['difficulty']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testUpdatePermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
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
        $call = $this->putJson('/forest/book/' . $book->id, $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testUpdateException(): void
    {
        $this->makeScopeManager($this->forestUser);
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
        $this->makeScopeManager($this->forestUser);
        $book = Book::first();

        $call = $this->delete('/forest/book/' . $book->id);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(204, $call->getStatusCode());
        $this->assertNull(Book::find($book->id));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
        $book = Book::first();
        $call = $this->deleteJson('/forest/book/' . $book->id);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyException(): void
    {
        $this->makeScopeManager($this->forestUser);
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
        $this->makeScopeManager($this->forestUser);
        $count = Book::count();
        $call = $this->get('/forest/book/count');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($count, $data['count']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testCountPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
        $call = $this->getJson('/forest/book/count');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyBulk(): void
    {
        $this->makeScopeManager($this->forestUser);
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
        $call = $this->delete('/forest/book/', $params);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(204, $call->getStatusCode());
        $this->assertTrue(empty(Book::where('id', '<=', 5)->get()->toArray()));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyBulkPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
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
        $call = $this->deleteJson('/forest/book/', $params);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDestroyBulkException(): void
    {
        $this->makeScopeManager($this->forestUser);
        Book::destroy([1, 2, 3, 4, 5]);
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
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['book' => 'id,label'], 'search' => '1'];
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
        $this->makeScopeManager($this->forestUser);
        $category = Category::whereHas('books')->first();
        $params = ['fields' => ['book' => 'id,label'], 'search' => $category->label, 'searchExtended' => 1];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::where('category_id', $category->id)->first();

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
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['book' => 'id,label'], 'search' => '9999'];
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
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['book' => 'id,label'], 'sort' => 'id'];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $books = Book::orderBy('id', 'ASC')->get();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($books->first()->id, $data['data'][0]['id']);
        $this->assertEquals($books->last()->id, $data['data'][$books->count() - 1]['id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSortDescWithQueryBuilder(): void
    {
        $this->makeScopeManager($this->forestUser);
        $params = ['fields' => ['book' => 'id,label'], 'sort' => '-id'];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $books = Book::orderBy('id', 'DESC')->get();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($books->first()->id, $data['data'][0]['id']);
        $this->assertEquals($books->last()->id, $data['data'][$books->count() - 1]['id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSortAscOnSmartField(): void
    {
        $this->makeScopeManager($this->forestUser);
        $books = Book::orderBy('label', 'ASC')->limit(3)->get();
        $params = ['fields' => ['book' => 'id,label,reference'], 'sort' => 'reference'];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($books->first()->id, $data['data'][0]['id']);
        $this->assertEquals($books->last()->id, $data['data'][2]['id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSortDescOnSmartField(): void
    {
        $this->makeScopeManager($this->forestUser);
        $books = Book::orderBy('label', 'DESC')->limit(3)->get();
        $params = ['fields' => ['book' => 'id,label,reference'], 'sort' => '-reference'];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($books->first()->id, $data['data'][0]['id']);
        $this->assertEquals($books->last()->id, $data['data'][2]['id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testFiltersWithQueryBuilder(): void
    {
        $this->makeScopeManager($this->forestUser);
        $book = Book::first();
        $params = ['fields' => ['book' => 'id,label'], 'filters' => '{"field":"label","operator":"equal","value":"' . $book->label . '"}'];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertCount(Book::where('label', $book->label)->count(), $data['data']);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testFiltersAggregatorWithQueryBuilder(): void
    {
        $this->makeScopeManager($this->forestUser);
        $book = Book::first();
        $params = [
            'fields'  => ['book' => 'id,label,difficulty'],
            'filters' => '{"aggregator":"and","conditions":[{"field":"label","operator":"equal","value":"' . $book->label . '"},{"field":"difficulty","operator":"equal","value":"' . $book->difficulty . '"}]}',
        ];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expectedCount = Book::where(['label' => $book->label, 'difficulty' => $book->difficulty])->count();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertCount($expectedCount, $data['data']);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals($book->difficulty, $data['data'][0]['attributes']['difficulty']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testFiltersOnSmartField(): void
    {
        $this->makeScopeManager($this->forestUser);
        $book = Book::first();
        $book->label = 'my favorite book';
        $book->difficulty = 'easy';
        $book->save();

        $params = ['fields' => ['book' => 'id,label,reference'], 'filters' => '{"field":"reference","operator":"equal","value":"my favorite book-easy"}'];
        $call = $this->get('/forest/book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertCount(1, $data['data']);
        $this->assertEquals('book', $data['data'][0]['type']);
        $this->assertEquals('my favorite book', $data['data'][0]['attributes']['label']);
    }

    /**
     * @return array
     */
    public function getScopesFromApi(): array
    {
        return [
            'book' => [
                'scope' => [
                    'filter'              => [
                        'aggregator' => 'and',
                        'conditions' => [
                            [
                                'field'    => 'difficulty',
                                'operator' => 'equal',
                                'value'    => 'hard',
                            ],
                            [
                                'field'    => 'label',
                                'operator' => 'equal',
                                'value'    => '$currentUser.firstName',
                            ],
                        ],
                    ],
                    'dynamicScopesValues' => [
                        'users' => [
                            '1' => [
                                '$currentUser.firstName' => 'foo',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
