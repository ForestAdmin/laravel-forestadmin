<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use Firebase\JWT\JWT;
use ForestAdmin\LaravelForestAdmin\Auth\AuthManager;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Http\Controllers\AuthController;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockIpWhitelist;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class AuthControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class AuthControllerTest extends TestCase
{
    use ProphecyTrait;
    use MockIpWhitelist;

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mockIpWhitelist();
    }

    /**
     * @var AuthController
     */
    private AuthController $authController;

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testLogout(): void
    {
        $call = $this->post('forest/authentication/logout');

        $this->assertEquals($call->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testLogin(): void
    {
        $params = ['renderingId' => 1];
        $request = Request::create('/forest/authentication', 'POST', $params);
        app()->instance('request', $request);
        $return = 'http://localhost/oidc/auth?state=%7B%22renderingId%22%3A28%7D&scope=openid%20profile%20email&response_type=code&approval_prompt=auto&redirect_uri=http%3A%2F%2Flocalhost%3A3000%2Fforest%2Fauthentication%2Fcallback&client_id=TEST';

        $auth = $this->prophesize(AuthManager::class);
        $auth
            ->start(1)
            ->shouldBeCalled()
            ->willReturn($return);

        $this->authController = new AuthController($auth->reveal());
        $login = $this->authController->login();

        $this->assertEquals($return, json_decode($login->getContent(), true)['authorizationUrl']);
    }


    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @throws IdentityProviderException
     * @return void
     */
    public function testCallback(): void
    {
        $user = [
            'id'           => 1,
            'email'        => 'john.doe@example.com',
            'first_name'   => 'John',
            'last_name'    => 'Doe',
            'team'         => 'Operations',
            'tags'         => [
                0 => [
                    'key'   => 'demo',
                    'value' => '1234',
                ],
            ],
            'rendering_id' => 1,
            'exp'          => (new \DateTime())->modify('+ 1 hour')->format('U'),
        ];
        $jwt = JWT::encode($user, config('forest.api.auth-secret'), 'HS256');

        $auth = $this->prophesize(AuthManager::class);
        $auth
            ->verifyCodeAndGenerateToken(Argument::any())
            ->shouldBeCalled()
            ->willReturn($jwt);

        $this->authController = new AuthController($auth->reveal());
        $callback = $this->authController->callback();

        $this->assertInstanceOf(JsonResponse::class, $callback);
        $content = json_decode($callback->getContent(), true);

        $this->assertEquals($jwt, $content['token']);
        $this->assertEquals($user, $content['tokenData']);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetAndCheckRenderingId(): void
    {
        $params = ['renderingId' => 1];
        $request = Request::create('/forest/authentication', 'POST', $params);
        app()->instance('request', $request);

        $this->authController = app(AuthController::class);
        $this->assertIsInt($this->invokeMethod($this->authController, 'getAndCheckRenderingId'));
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetAndCheckExceptionMissingRenderingId(): void
    {
        $params = [];
        $request = Request::create('/forest/authentication', 'POST', $params);
        app()->instance('request', $request);

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::MISSING_RENDERING_ID);

        $this->authController = app(AuthController::class);
        $this->invokeMethod($this->authController, 'getAndCheckRenderingId');
    }


    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetAndCheckExceptionInvalidRenderingId(): void
    {
        $params = ['renderingId' => 10.1];
        $request = Request::create('/forest/authentication', 'POST', $params);
        app()->instance('request', $request);

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::INVALID_RENDERING_ID);

        $this->authController = app(AuthController::class);
        $this->invokeMethod($this->authController, 'getAndCheckRenderingId');
    }
}
