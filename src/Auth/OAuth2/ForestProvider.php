<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\OAuth2;

use ForestAdmin\LaravelForestAdmin\Exceptions\AuthorizationException;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ForestProvider
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestProvider extends AbstractProvider
{
    use FormatGuzzle;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $renderingId;

    /**
     * @param string $host
     * @param array  $options
     */
    public function __construct(string $host, array $options = [])
    {
        parent::__construct($options);
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->host . '/oidc/auth';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->host . '/oidc/token';
    }

    /**
     * @param AccessToken $token
     * @return string
     * @throws \Exception
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->host . '/liana/v2/renderings/' . $this->renderingId . '/authorization';
    }

    /**
     * @param int $renderingId
     * @return ForestProvider
     */
    public function setRenderingId(int $renderingId): ForestProvider
    {
        $this->renderingId = $renderingId;
        return $this;
    }

    /**
     * @return string[]
     */
    protected function getRequiredOptions()
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
            'host',
        ];
    }

    /**
     * @return string[]
     */
    protected function getDefaultScopes()
    {
        return ['openid profile email'];
    }

    /**
     * @param ResponseInterface $response
     * @param array|string      $data
     * @return void
     * @throws \Exception
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (404 === $response->getStatusCode()) {
            throw new AuthorizationException(ErrorMessages::SECRET_NOT_FOUND);
        } elseif (422 === $response->getStatusCode()) {
            throw new AuthorizationException(ErrorMessages::SECRET_AND_RENDERINGID_INCONSISTENT);
        } elseif (200 !== $response->getStatusCode()) {
            $serverError = (array_key_exists('errors', $data) && count($data['errors']) > 0) ? $data['errors'][0] : null;
            if (null !== $serverError && array_key_exists('name', $serverError) && $serverError['name'] === ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED) {
                throw new AuthorizationException(ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED);
            }

            throw new \Exception(ErrorMessages::AUTHORIZATION_FAILED);
        }
    }

    /**
     * @param array       $response
     * @param AccessToken $token
     * @return ResourceOwnerInterface|void
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ForestResourceOwner($response, $this->renderingId);
    }

    /**
     * @param AccessToken $token
     * @return array|mixed|void
     * @throws GuzzleException
     * @throws \JsonException
     * @throws \Exception
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $url = $this->getResourceOwnerDetailsUrl($token);

        $request = $this->getAuthenticatedRequest(
            self::METHOD_GET,
            $url,
            $token,
            [
                'headers' => [
                    'forest-token'      => $token->getToken(),
                    'forest-secret-key' => config('forest.api.secret')
                ],
            ]
        );

        $response = $this->getParsedResponse($request);
        $userData = $response['data']['attributes'];
        $userData['id'] = $response['data']['id'];

        return $userData;
    }
}
