<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Exports\CollectionExport;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockIpWhitelist;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class IpWhitelistTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class IpWhitelistTest extends TestCase
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
     * @throws JsonException
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
        $this->mockIpWhitelist(true);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testIpValid(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->getJson('/forest/book', ['REMOTE_ADDR' => '127.0.0.1']);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_OK, $call->getStatusCode());
        $this->assertIsArray($data['data']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testIpInvalid(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->getJson('/forest/book', ['REMOTE_ADDR' => '129.0.0.1']);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('IP address rejected (129.0.0.1)', $data['message']);
        $this->assertEquals(HttpException::class, $data['exception']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testIpInRange(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->getJson('/forest/book', ['REMOTE_ADDR' => '100.2.3.15']);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_OK, $call->getStatusCode());
        $this->assertIsArray($data['data']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testIpSubNet(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->getJson('/forest/book', ['REMOTE_ADDR' => '180.10.10.20']);
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Response::HTTP_OK, $call->getStatusCode());
        $this->assertIsArray($data['data']);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testIpWhitelistCanNotFetchApi(): void
    {
        $this->makeScopeManager($this->forestUser);
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::UNEXPECTED);
        new IpWhitelist(new ForestApiRequester());
    }
}
