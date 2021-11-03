<?php

namespace ForestAdmin\LaravelForestAdmin\Auth;

use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestProvider;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzleBody;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

/**
 * Class OidcClientManager
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class OidcClientManager
{
    use FormatGuzzle;

    public const TTL = 60 * 60 * 24;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @param ForestApiRequester $forestApi
     */
    public function __construct(ForestApiRequester $forestApi)
    {
        $this->forestApi = $forestApi;
    }

    /**
     * @param string $callbackUrl
     * @return ForestProvider|string
     * @throws GuzzleException
     */
    public function getClientForCallbackUrl(string $callbackUrl)
    {
        $cacheKey = $callbackUrl . '-' . config('forest.api.secret') . '-client-data';

        try {
            $config = $this->retrieve();
            Cache::remember(
                $cacheKey,
                self::TTL,
                function () use ($config, $callbackUrl) {
                    $clientCredentials = $this->register(
                        [
                            'token_endpoint_auth_method' => 'none',
                            'registration_endpoint'      => $config['registration_endpoint'],
                            'redirect_uris'              => [$callbackUrl],
                            'application_type'           => 'web'
                        ]
                    );
                    $clientData = ['client_id' => $clientCredentials['client_id'], 'issuer' => $config['issuer']];

                    return $clientData;
                }
            );
        } catch (\Exception) {
            throw new ForestApiException(ErrorMessages::REGISTRATION_FAILED);
        }

        return new ForestProvider(
            Cache::get($cacheKey)['issuer'],
            [
                'clientId'     => Cache::get($cacheKey)['client_id'],
                'redirectUri'  => $callbackUrl,
            ]
        );
    }

    /**
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    private function retrieve(): array
    {
        try {
            $response = $this->forestApi->get('/oidc/.well-known/openid-configuration');
        } catch (\RuntimeException) {
            throw new ForestApiException(ErrorMessages::OIDC_CONFIGURATION_RETRIEVAL_FAILED);
        }

        return $this->getBody($response);
    }

    /**
     * @param array $data
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    private function register(array $data): array
    {
        $response = $this->forestApi->post(
            $data['registration_endpoint'],
            [],
            $data,
            ['Authorization' => 'Bearer ' . config('forest.api.secret')]
        );

        return $this->getBody($response);
    }
}
