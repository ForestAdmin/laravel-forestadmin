<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Middleware;

use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;

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
            dd($ipWhitelist->isIpMatchesAnyRule($request->ip()));
        }

        // if not enabled -> next($request)
        // if enabled
            // - test isIpMatchesAnyRule
                    // OK NEXT
                    // KO 403
    }
}
