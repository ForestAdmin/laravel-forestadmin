<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestProvider;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Exceptions\AuthorizationException;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockClientHttp;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class ForestProviderTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestProviderTest extends TestCase
{
    use MockClientHttp;

    /**
     * @var ForestProvider
     */
    protected ForestProvider $provider;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new ForestProvider(
            'mock-host',
            [
                'clientId'    => 'mock-client-id',
                'redirectUri' => 'mock-redirect-uri'
            ]
        );
    }

    /**
     * @return void
     */
    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
    }

    /**
     * @return void
     */
    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('mock-host/oidc/token', $uri['path']);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testGetResourceOwnerDetailsUrl(): void
    {
        $this->provider->setRenderingId(1234);
        $token = new AccessToken(['access_token' => 'mock_access_token']);
        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);

        $this->assertEquals('mock-host/liana/v2/renderings/1234/authorization', $uri['path']);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testGetResourceOwnerDetailsUrlException(): void
    {
        $token = new AccessToken(['access_token' => 'mock_access_token']);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Typed property ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestProvider::$renderingId must not be accessed before initialization');

        $this->provider->getResourceOwnerDetailsUrl($token);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetRequiredOptions(): void
    {
        $requiredOptions = $this->invokeMethod($this->provider, 'getRequiredOptions');

        $this->assertEqualsCanonicalizing(['urlAuthorize', 'urlAccessToken', 'host'], $requiredOptions);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testSetRenderingId(): void
    {
        $value = 1234;
        $this->provider->setRenderingId($value);

        $this->assertEquals($value, $this->invokeProperty($this->provider, 'renderingId'));
    }

    /**
     * @throws IdentityProviderException
     * @return void
     */
    public function testGetAccessToken(): void
    {
        $this->mockClient();
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
    }

    /**
     * @throws IdentityProviderException
     * @return void
     */
    public function testGetAccessTokenExceptionNotFound(): void
    {
        $this->mockClient(404);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(ErrorMessages::SECRET_NOT_FOUND);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @throws IdentityProviderException
     * @return void
     */
    public function testGetAccessTokenExceptionSecretAndRenderingIdInconsistent(): void
    {
        $this->mockClient(422);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(ErrorMessages::SECRET_AND_RENDERINGID_INCONSISTENT);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @throws IdentityProviderException
     * @return void
     */
    public function testGetAccessTokenExceptionTwoFactorAuthenticationRequired(): void
    {
        $this->mockClient(400, '{"errors":[{"name":"' . ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED . '"}]}');

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @throws IdentityProviderException
     * @return void
     */
    public function testGetAccessTokenException(): void
    {
        $this->mockClient(400);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ErrorMessages::AUTHORIZATION_FAILED);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    /**
     * @throws IdentityProviderException
     * @return void
     */
    public function testGetResourceOwner(): void
    {
        $this->mockClient(
            200,
            '{
              "access_token":"mock_access_token", 
              "data": {
                "type": "users",
                "id": "1",
                "attributes": {
                  "first_name": "John",
                  "last_name": "Doe",
                  "email": "jdoe@forestadmin.com",
                  "teams": {
                    "0": "Operations"
                  },
                  "tags": [],
                  "two_factor_authentication_enabled": false,
                  "two_factor_authentication_active": false
                }
              }
            }',
            2,
            2
        );
        $this->provider->setRenderingId(1234);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $resourceOwner = $this->provider->getResourceOwner($token);

        $this->assertInstanceOf(ForestResourceOwner::class, $resourceOwner);
    }
}
