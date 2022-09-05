<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestProvider;
use ForestAdmin\LaravelForestAdmin\Auth\OidcClientManager;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class OidcClientManagerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class OidcClientManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

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
        $this->oidc = new OidcClientManager($this->makeForestApiGet());
        $retrieve = $this->invokeMethod($this->oidc, 'retrieve');
        $this->assertIsArray($retrieve);
        $this->assertEquals($retrieve, self::mockedConfig());
    }

    /**
     * @throws \JsonException
     * @throws \ReflectionException
     * @return void
     */
    public function testRetrieveException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(ErrorMessages::OIDC_CONFIGURATION_RETRIEVAL_FAILED);
        $this->oidc = new OidcClientManager($this->makeForestApiGetThrowException());

        $this->invokeMethod($this->oidc, 'retrieve');
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testRegister(): void
    {
        $data = [
            'token_endpoint_auth_method' => 'none',
            'registration_endpoint'      => self::mockedConfig()['registration_endpoint'],
            'redirect_uris'              => ['mock_host/callback'],
            'application_type'           => 'web'
        ];
        $this->oidc = new OidcClientManager($this->makeForestApiPost($data));
        $register = $this->invokeMethod(
            $this->oidc,
            'register',
            array(&$data)
        );
        $this->assertIsArray($register);
        $this->assertEquals(1, $register['client_id']);
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testMakeForestProvider(): void
    {
        $this->oidc = new OidcClientManager($this->makeForestApiGetAndPost(json_encode(['client_id' => 1, 'redirect_uris' => ['http://backend.api']], JSON_THROW_ON_ERROR)));
        $clientForCallbackUrl = $this->oidc->makeForestProvider();

        $this->assertInstanceOf(ForestProvider::class, $clientForCallbackUrl);
        $this->assertIsArray(Cache::get(config('forest.api.secret') . '-client-data'));
        $this->assertSame(
            Cache::get(config('forest.api.secret') . '-client-data'),
            ['client_id' => 1, 'issuer' => self::mockedConfig()['issuer'], 'redirect_uri' => 'http://backend.api']
        );
    }

    /**
     * @throws GuzzleException
     * @throws \JsonException
     * @return void
     */
    public function testMakeForestProviderException(): void
    {
        $this->oidc = new OidcClientManager($this->makeForestApiGetAndPost());
        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::REGISTRATION_FAILED);
        $this->oidc->makeForestProvider();
    }

    /**
     * @return string
     */
    public static function mockedConfig()
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
                "urn:ietf:params:oauth:grant-type:device_code"
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

    /**
     * @return object
     * @throws \JsonException
     */
    public function makeForestApiGet()
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], json_encode(self::mockedConfig(), JSON_THROW_ON_ERROR))
            );

        return $forestApiGet->reveal();
    }

    /**
     * @return object
     */
    public function makeForestApiGetThrowException()
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'))
            ->shouldBeCalled()
            ->willThrow(new \RuntimeException());

        return $forestApiGet->reveal();
    }

    /**
     * @param array $data
     * @return object
     * @throws \JsonException
     */
    public function makeForestApiPost(array $data)
    {
        $forestApiPost = $this->prophesize(ForestApiRequester::class);
        $forestApiPost
            ->post(Argument::type('string'), Argument::size(0), $data, Argument::size(1))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], json_encode(['client_id' => 1], JSON_THROW_ON_ERROR))
            );

        return $forestApiPost->reveal();
    }

    /**
     * @param string|null $body
     * @return object
     * @throws \JsonException
     */
    public function makeForestApiGetAndPost(?string $body = null)
    {
        $forestApi = $this->prophesize(ForestApiRequester::class);
        $forestApi
            ->get(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], json_encode(self::mockedConfig(), JSON_THROW_ON_ERROR))
            );

        $forestApi
            ->post(Argument::type('string'), Argument::size(0), Argument::size(3), Argument::size(1))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], $body)
            );

        return $forestApi->reveal();
    }
}
