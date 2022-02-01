<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Middleware;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ForestAuthorization
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestAuthorization
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
     * @param          $request
     * @param \Closure $next
     * @return mixed|void
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function handle($request, \Closure $next)
    {
        if (Auth::guard('forest')->check()) {
            $forestUser = Auth::guard('forest')->user();
            $forestUser->setPermissions(
                $this->getPermissions($forestUser->getKey(), $forestUser->getAttribute('rendering_id'))
            );

            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    /**
     * @param int $userId
     * @param int $renderingId
     * @return array
     */
    private function getPermissions(int $userId, int $renderingId): array
    {
        $cacheKey = 'user-' . $userId . ':rendering-' . $renderingId;

        return Cache::remember(
            $cacheKey,
            self::TTL,
            function () use ($renderingId) {
                $permissions = $this->fetchPermissions($renderingId);

                if (array_key_exists('data', $permissions)) {
                    $permissions['collections'] = $permissions['data']['collections'];
                    $permissions['renderings'] = $permissions['data']['renderings'];
                    unset($permissions['data']);
                }

                return $permissions;
            }
        );
    }

    /**
     * @param int $renderingId
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    private function fetchPermissions(int $renderingId): array
    {
        try {
            $response = $this->forestApi->get('/liana/v3/permissions', compact('renderingId'));
        } catch (\RuntimeException $e) {
            throw new ForestApiException(ErrorMessages::UNEXPECTED);
        }

        return $this->getBody($response);
    }
}
