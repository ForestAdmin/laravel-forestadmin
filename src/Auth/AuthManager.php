<?php

namespace ForestAdmin\LaravelForestAdmin\Auth;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JsonException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class AuthManager
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class AuthManager
{
    use FormatGuzzle;

    /**
     * @var OidcClientManager
     */
    private OidcClientManager $oid;

    /**
     * @param OidcClientManager $oidc
     */
    public function __construct(OidcClientManager $oidc)
    {
        $this->oidc = $oidc;
    }

    /**
     * @param int $renderingId
     * @return string
     * @throws GuzzleException
     * @throws JsonException
     */
    public function start(int $renderingId)
    {
        $client = $this->oidc->makeForestProvider();

        return $client->getAuthorizationUrl(
            [
                'state' => json_encode(compact('renderingId'), JSON_THROW_ON_ERROR),
            ]
        );
    }

    /**
     * @param array $params
     * @return string
     * @throws GuzzleException
     * @throws IdentityProviderException
     * @throws JsonException
     */
    public function verifyCodeAndGenerateToken(array $params): string
    {
        $this->stateIsValid($params);

        $forestProvider = $this->oidc->makeForestProvider();
        $forestProvider->setRenderingId($this->getRenderingIdFromState($params['state']));
        if (config('app.debug')) {
            // @codeCoverageIgnoreStart
            $guzzleClient = new Client([RequestOptions::VERIFY => false]);
            $forestProvider->setHttpClient($guzzleClient);
            // @codeCoverageIgnoreEnd
        }

        $accessToken = $forestProvider->getAccessToken(
            'authorization_code',
            [
                'code'          => $params['code'],
                'response_type' => 'token'
            ]
        );

        $resourceOwner = $forestProvider->getResourceOwner($accessToken);

        return $resourceOwner->makeJwt();
    }

    /**
     * @param string $state
     * @return int
     * @throws JsonException
     */
    private function getRenderingIdFromState(string $state): int
    {
        $state = json_decode($state, true, 512, JSON_THROW_ON_ERROR);
        $renderingId = $state['renderingId'];

        if (!(is_string($renderingId) || is_int($renderingId))) {
            throw new ForestApiException(ErrorMessages::INVALID_STATE_FORMAT);
        }

        return (int) $renderingId;
    }

    /**
     * @param array $params
     * @return bool
     * @throws JsonException
     */
    private function stateIsValid(array $params): bool
    {
        if (!array_key_exists('state', $params)) {
            throw new ForestApiException(ErrorMessages::INVALID_STATE_MISSING);
        }

        if (!array_key_exists('renderingId', json_decode($params['state'], true, 512, JSON_THROW_ON_ERROR))) {
            throw new ForestApiException(ErrorMessages::INVALID_STATE_RENDERING_ID);
        }

        return true;
    }
}
