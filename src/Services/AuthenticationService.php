<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AuthenticationService
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class AuthenticationService extends AbstractProvider
{
    /**
     * @var string
     */
    private string $host;

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
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return '';
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
     * Returns authorization parameters based on provided options.
     *
     * @param array $options
     * @return array Authorization parameters
     */
    protected function getAuthorizationParameters(array $options)
    {
        $options += [
            'response_type' => 'code',
            'scope'         => $this->getDefaultScopes(),
        ];

        if (is_array($options['scope'])) {
            $separator = $this->getScopeSeparator();
            $options['scope'] = implode($separator, $options['scope']);
        }

        // Store the state as it may need to be accessed later on.
        $this->state = $options['state'];

        // Business code layer might set a different redirect_uri parameter
        // depending on the context, leave it as-is
        if (!isset($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->redirectUri;
        }

        $options['client_id'] = $this->clientId;


        return $options;
    }

    /**
     * @param ResponseInterface $response
     * @param array|string      $data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        // TODO: Implement checkResponse() method.
    }

    /**
     * @param array       $response
     * @param AccessToken $token
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface|void
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        // TODO: Implement createResourceOwner() method.
    }
}
