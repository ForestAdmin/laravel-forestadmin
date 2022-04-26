<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Middleware;

use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class IpWhitelistAuthorization
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class IpWhitelistAuthorization
{
    /**
     * @param          $request
     * @param \Closure $next
     * @return mixed
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function handle($request, \Closure $next)
    {
        /** @var IpWhitelist $ipWhitelist */
        $ipWhitelist = app(IpWhitelist::class);
        if ($ipWhitelist->isEnabled()) {
            $ip = $request->ip();
            if ($ipWhitelist->isIpMatchesAnyRule($ip)) {
                return $next($request);
            } else {
                throw new HttpException(Response::HTTP_FORBIDDEN, "IP address rejected ($ip)");
            }
        }

        return $next($request);
    }
}
