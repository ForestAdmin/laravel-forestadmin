<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\OAuth2;

use ForestAdmin\LaravelForestAdmin\Exceptions\AuthorizationException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
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
     * @throws \Exception
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        if (null === $this->renderingId) {
            throw new \Exception('The renderingId parameter must be set before using the resourceOwner url');
        }

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
     * @throws \Exception
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (200 !== $response->getStatusCode()) {
            if (404 === $response->getStatusCode()) {
                throw new AuthorizationException(ErrorMessages::SECRET_NOT_FOUND);
            }

            if (422 === $response->getStatusCode()) {
                throw new AuthorizationException(ErrorMessages::SECRET_AND_RENDERINGID_INCONSISTENT);
            }

            $serverError = (array_key_exists('errors', $data) && count($data['errors']) > 0) ? $data['errors'][0] : null;
            if (null !== $serverError && array_key_exists('name', $serverError) && $serverError['name'] === ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED) {
                throw new AuthorizationException(ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED);
            }

            throw new \Exception(ErrorMessages::AUTHORIZATION);
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

        $forestApi = new ForestApiRequester();
        $response = $forestApi->get($url, [], ['forest-token' => $token->getToken()]);

        if (200 !== $response->getStatusCode()) {
            return $this->throwAuthorizationException($response);
        }

        $body = $this->getBody($response);
        $userData = $body['data']['attributes'];
        $userData['id'] = $body['data']['id'];

        return $userData;
    }

    /**
     * @param Response $response
     * @throws \Exception
     * @return void
     */
    private function throwAuthorizationException(Response $response): void
    {
        if (404 === $response->getStatusCode()) {
            throw new AuthorizationException(ErrorMessages::SECRET_NOT_FOUND);
        }

        if (422 === $response->getStatusCode()) {
            throw new AuthorizationException(ErrorMessages::SECRET_AND_RENDERINGID_INCONSISTENT);
        }

        $body = $this->body($response);
        $serverError = (array_key_exists('errors', $body) && count($body['errors']) > 0) ? $body['errors'][0] : null;
        if (null !== $serverError && array_key_exists('name', $serverError) && $serverError['name'] === ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED) {
            throw new AuthorizationException(ErrorMessages::TWO_FACTOR_AUTHENTICATION_REQUIRED);
        }

        throw new \Exception(ErrorMessages::AUTHORIZATION);
    }
}
