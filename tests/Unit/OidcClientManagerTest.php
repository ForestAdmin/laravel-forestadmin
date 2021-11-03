<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestProvider;
use ForestAdmin\LaravelForestAdmin\Auth\OidcClientManager;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;

/**
 * Class OidcClientManagerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class OidcClientManagerTest extends TestCase
{
    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->forestApi = new ForestApiRequester();
    }

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('forest.api.url', 'mock_host');
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     * @return void
     */
    public function testRetrieve(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode($this->mockedConfig(), JSON_THROW_ON_ERROR)),]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->forestApi->setClient($client);
        $this->oidc = new OidcClientManager($this->forestApi);
        $retrieve = $this->invokeMethod($this->oidc, 'retrieve');
        $this->assertIsArray($retrieve);
        $this->assertEquals($retrieve, $this->mockedConfig());
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     * @return void
     */
    public function testRetrieveException(): void
    {
        $mock = new MockHandler([new Response(404, [], json_encode($this->mockedConfig(), JSON_THROW_ON_ERROR)),]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->forestApi->setClient($client);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(ErrorMessages::OIDC_CONFIGURATION_RETRIEVAL_FAILED);
        $this->oidc = new OidcClientManager($this->forestApi);
        $this->invokeMethod($this->oidc, 'retrieve');
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testRegister(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode(['client_id' => 1], JSON_THROW_ON_ERROR)),]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->forestApi->setClient($client);
        $this->oidc = new OidcClientManager($this->forestApi);
        $register = $this->oidc->register(
            [
                'token_endpoint_auth_method' => 'none',
                'registration_endpoint'      => $this->mockedConfig()['registration_endpoint'],
                'redirect_uris'              => ['mock_host/callback'],
                'application_type'           => 'web'
            ]
        );
        $this->assertIsArray($register);
        $this->assertEquals(1, $register['client_id']);
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testGetClientForCallbackUrl(): void
    {
        $mock = new MockHandler(
            [
                new Response(200, [], json_encode($this->mockedConfig(), JSON_THROW_ON_ERROR)),
                new Response(200, [], json_encode(['client_id' => 1], JSON_THROW_ON_ERROR)),
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->forestApi->setClient($client);
        $this->oidc = new OidcClientManager($this->forestApi);
        $clientForCallbackUrl = $this->oidc->getClientForCallbackUrl('mock_host/foo');

        $this->assertInstanceOf(ForestProvider::class, $clientForCallbackUrl);
        $this->assertIsArray(Cache::get('mock_host/foo-' . config('forest.api.secret') . '-client-data'));
        $this->assertSame(
            Cache::get('mock_host/foo-' . config('forest.api.secret') . '-client-data'),
            ['client_id' => 1, 'issuer' => $this->mockedConfig()['issuer']]
        );
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testGetClientForCallbackUrlException(): void
    {
        $mock = new MockHandler(
            [
                new Response(200, [], json_encode($this->mockedConfig(), JSON_THROW_ON_ERROR)),
                new Response(200, [], ''),
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->forestApi->setClient($client);
        $this->oidc = new OidcClientManager($this->forestApi);
        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::REGISTRATION_FAILED);
        $this->oidc->getClientForCallbackUrl('mock_host/foo');
    }

    /**
     * @return string
     */
    private function mockedConfig()
    {
        return [
            "authorization_endpoint"                           => "https://mock_host/oidc/auth",
            "device_authorization_endpoint"                    => "https://mock_host/oidc/device/auth",
            "claims_parameter_supported"                       => false,
            "claims_supported"                                 => [
                "sub",
                "email",
                "sid",
                "auth_time",
                "iss"
            ],
            "code_challenge_methods_supported"                 => [
                "S256"
            ],
            "end_session_endpoint"                             => "https://mock_host/oidc/session/end",
            "grant_types_supported"                            => [
                "authorization_code",
                "urn=>ietf=>params=>oauth=>grant-type=>device_code"
            ],
            "id_token_signing_alg_values_supported"            => [
                "HS256",
                "RS256"
            ],
            "issuer"                                           => "https://mock_host",
            "jwks_uri"                                         => "https://mock_host/oidc/jwks",
            "registration_endpoint"                            => "https://mock_host/oidc/reg",
            "response_modes_supported"                         => [
                "query"
            ],
            "response_types_supported"                         => [
                "code",
                "none"
            ],
            "scopes_supported"                                 => [
                "openid",
                "email",
                "profile"
            ],
            "subject_types_supported"                          => [
                "public"
            ],
            "token_endpoint_auth_methods_supported"            => [
                "none"
            ],
            "token_endpoint_auth_signing_alg_values_supported" => [],
            "token_endpoint"                                   => "https://mock_host/oidc/token",
            "request_object_signing_alg_values_supported"      => [
                "HS256",
                "RS256"
            ],
            "request_parameter_supported"                      => false,
            "request_uri_parameter_supported"                  => true,
            "require_request_uri_registration"                 => true,
            "claim_types_supported"                            => [
                "normal"
            ]
        ];
    }
}
