<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Exports\CollectionExport;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SmartActionControllerTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartActionControllerTest extends TestCase
{
    use FakeData;
    use FakeSchema;
    use MockForestUserFactory;
    use ScopeManagerFactory;

    /**
     * @var ForestUser
     */
    private ForestUser $forestUser;

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
    public function setUp(): void
    {
        parent::setUp();

        $this->forestUser = new ForestUser(
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
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSmartAction(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->postJson('/forest/smart-actions/book_smart-action-single');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expected = [
            'success' => 'Test working!'
        ];

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSmartActionGlobal(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $payload = [
            'data' => [
                'attributes'    => [
                    'ids' => ['1', '2', '3'],
                    'all_records' => false,
                    'all_records_ids_excluded' => []
                ],
            ],
        ];
        $call = $this->postJson('/forest/smart-actions/book_smart-action-bulk', $payload);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expected = [
            'success' => 'ids => 1,2,3'
        ];

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSmartActionPermissionDenied(): void
    {
        $this->makeScopeManager($this->forestUser);
        $this->mockForestUserFactory(false);
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->postJson('/forest/smart-actions/book_smart-action-single');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $call->baseResponse->getStatusCode());
        $this->assertEquals('This action is unauthorized.', $data['message']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSmartActionNotFoundException(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 There is no smart-action smart-action-foo");
        $this->postJson('/forest/smart-actions/book_smart-action-foo');
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSmartActionWithLoadHook(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->postJson('/forest/smart-actions/book_smart-action-single/hooks/load');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $expected = [
            'fields' => [
                [
                    'field'         => 'token',
                    'type'          => 'string',
                    'is_required'   => true,
                    'is_read_only'  => false,
                    'default_value' => null,
                    'reference'     => null,
                    'description'   => null,
                    'hook'          => null,
                    'enums'         => null,
                    'value'         => 'default',
                ],
                [
                    'field'         => 'foo',
                    'type'          => 'string',
                    'is_required'   => true,
                    'is_read_only'  => false,
                    'default_value' => null,
                    'reference'     => null,
                    'description'   => null,
                    'hook'          => 'onFooChange',
                    'enums'         => null,
                    'value'         => null,
                ],
            ],
        ];

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testSmartActionWithLoadChange(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::partialMock()->shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $payload = [
            'data' => [
                'attributes'    => [
                    'fields' => [
                        [
                            'field'         => 'token',
                            'type'          => 'string',
                            'is_required'   => true,
                            'is_read_only'  => false,
                            'default_value' => null,
                            'reference'     => null,
                            'description'   => null,
                            'hook'          => null,
                            'enums'         => null,
                            'value'         => 'default',
                        ],
                        [
                            'field'         => 'foo',
                            'type'          => 'string',
                            'is_required'   => true,
                            'is_read_only'  => false,
                            'default_value' => null,
                            'reference'     => null,
                            'description'   => null,
                            'hook'          => 'onFooChange',
                            'enums'         => null,
                            'value'         => null,
                        ],
                    ],
                    'changed_field' => 'foo',
                ],
            ],
        ];
        $call = $this->postJson('/forest/smart-actions/book_smart-action-single/hooks/change', $payload);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $expected = [
            'fields' => [
                [
                    'field'         => 'token',
                    'type'          => 'string',
                    'is_required'   => true,
                    'is_read_only'  => false,
                    'default_value' => null,
                    'reference'     => null,
                    'description'   => null,
                    'hook'          => null,
                    'enums'         => null,
                    'value'         => 'Test onChange Foo',
                ],
                [
                    'field'         => 'foo',
                    'type'          => 'string',
                    'is_required'   => true,
                    'is_read_only'  => false,
                    'default_value' => null,
                    'reference'     => null,
                    'description'   => null,
                    'hook'          => 'onFooChange',
                    'enums'         => null,
                    'value'         => null,
                ],
            ],
        ];

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($expected, $data);
    }
}
