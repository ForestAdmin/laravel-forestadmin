<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Auth\AuthManager;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestProvider;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Auth\OidcClientManager;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use Illuminate\Contracts\Container\BindingResolutionException;
use League\OAuth2\Client\Token\AccessToken;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class AuthManagerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class AuthManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @throws BindingResolutionException
     * @return void
     */
    public function testStart(): void
    {
        app()['config']->set('app.debug', false);
        $return = '123ABC';
        $oidc = $this->makeOidc($return, true);

        $this->auth = app()->make(AuthManager::class);
        $this->auth->oidc = $oidc->reveal();

        $start = $this->auth->start(1);
        parse_str(parse_url($start, PHP_URL_QUERY), $output);

        $this->assertSame($output['state'], '{"renderingId":1}');
    }

    /**
     * @throws BindingResolutionException
     * @return void
     */
    public function testVerifyCodeAndGenerateToken(): void
    {
        app()['config']->set('app.debug', false);
        $return = '123ABC';
        $oidc = $this->makeOidc($return);

        /** @var AuthManager auth */
        $this->auth = app()->make(AuthManager::class);
        $this->auth->oidc = $oidc->reveal();

        $data = ['code' => 'test', 'state' => '{"renderingId":1}'];
        $token = $this->auth->verifyCodeAndGenerateToken($data);

        $this->assertSame($return, $token);
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @return void
     */
    public function testGetRenderingIdFromState(): void
    {
        /** @var AuthManager auth */
        $this->auth = app()->make(AuthManager::class);
        $data = ['state' => '{"renderingId":1}'];
        $renderingId = $this->invokeMethod($this->auth, 'getRenderingIdFromState', $data);

        $this->assertSame($renderingId, 1);
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @return void
     */
    public function testGetRenderingIdFromStateException(): void
    {
        /** @var AuthManager auth */
        $this->auth = app()->make(AuthManager::class);
        $data = ['state' => '{"renderingId":10.1}'];

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::INVALID_STATE_FORMAT);

        $this->invokeMethod($this->auth, 'getRenderingIdFromState', $data);
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @return void
     */
    public function testStateIsValid(): void
    {
        /** @var AuthManager auth */
        $this->auth = app()->make(AuthManager::class);
        $data = ['code' => 'test', 'state' => '{"renderingId":1}'];
        $state = $this->invokeMethod($this->auth, 'stateIsValid', array(&$data));

        $this->assertTrue($state);
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @return void
     */
    public function testStateException(): void
    {
        /** @var AuthManager auth */
        $this->auth = app()->make(AuthManager::class);
        $data = ['code' => 'test'];

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::INVALID_STATE_MISSING);


        $this->invokeMethod($this->auth, 'stateIsValid', array(&$data));
    }

    /**
     * @throws BindingResolutionException
     * @throws \ReflectionException
     * @return void
     */
    public function testRenderingStateException(): void
    {
        /** @var AuthManager auth */
        $this->auth = app()->make(AuthManager::class);
        $data = ['code' => 'test', 'state' => '{}'];

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::INVALID_STATE_RENDERING_ID);

        $this->invokeMethod($this->auth, 'stateIsValid', array(&$data));
    }

    /**
     * @param string $return
     * @param false  $withAuthorization
     * @return ObjectProphecy
     */
    private function makeOidc(string $return, $withAuthorization = false): ObjectProphecy
    {
        $resourceOwner = $this->prophesize(ForestResourceOwner::class);
        $resourceOwner
            ->makeJwt()
            ->willReturn($return);

        $provider = $this->prophesize(ForestProvider::class);
        $provider
            ->getAccessToken(Argument::type('string'), Argument::size(2))
            ->willReturn(
                new AccessToken(['access_token' => 'token'])
            );
        $provider
            ->getResourceOwner(Argument::any())
            ->willReturn(
                $resourceOwner->reveal()
            );
        $provider
            ->setRenderingId(Argument::type('integer'))
            ->willReturn($provider);

        if ($withAuthorization) {
            $provider->getAuthorizationUrl(Argument::any())
                ->shouldBeCalled()
                ->willReturn(
                    'http://localhost/oidc/auth?state=%7B%22renderingId%22%3A1%7D&scope=openid%20profile%20email&response_type=code&approval_prompt=auto&redirect_uri=http%3A%2F%2Flocalhost%2Ffoo%2Fauthentication%2Fcallback&client_id=TEST'
                );
        }

        $oidc = $this->prophesize(OidcClientManager::class);
        $oidc
            ->makeForestProvider()
            ->shouldBeCalled()
            ->willReturn(
                $provider->reveal()
            );

        return $oidc;
    }
}
