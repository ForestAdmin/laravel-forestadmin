<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ForestAdmin\LaravelForestAdmin\Auth\AuthManager;
use ForestAdmin\LaravelForestAdmin\Auth\OidcConfiguration;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use JsonException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Class AuthController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class AuthController extends ForestController
{
    /**
     * @var AuthManager
     */
    private AuthManager $auth;

    /**
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return JsonResponse
     * @throws GuzzleException
     * @throws JsonException
     */
    public function login()
    {
        $renderingId = $this->getAndCheckRenderingId();

        return response()->json(
            [
                'authorizationUrl' => $this->auth->start($renderingId),
            ]
        );
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        return response()->noContent();
    }

    /**
     * @return JsonResponse
     * @throws GuzzleException
     * @throws JsonException
     * @throws IdentityProviderException
     */
    public function callback()
    {
        $token = $this->auth->verifyCodeAndGenerateToken(request()->all());
        $tokenData = JWT::decode($token, new Key(config('forest.api.auth-secret'), 'HS256'));

        return response()->json(compact('token', 'tokenData'));
    }

    /**
     * @return int
     */
    private function getAndCheckRenderingId(): int
    {
        if (!$renderingId = request()->input('renderingId')) {
            throw new ForestApiException(ErrorMessages::MISSING_RENDERING_ID);
        }

        if (!(is_string($renderingId) || is_int($renderingId))) {
            throw new ForestApiException(ErrorMessages::INVALID_RENDERING_ID);
        }

        return (int) $renderingId;
    }
}
