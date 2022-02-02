<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Middleware;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\ForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Auth\Model\ForestUser;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
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
            app(ForestUserFactory::class)->makePermissionToUser($forestUser, $forestUser->getAttribute('rendering_id'));

            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
