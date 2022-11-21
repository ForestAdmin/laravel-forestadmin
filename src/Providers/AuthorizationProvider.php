<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Class AuthorizationProvider
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class AuthorizationProvider extends AuthServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest(
            'forest-token',
            static function (Request $request) {
                if ($request->bearerToken()) {
                    $tokenData = JWT::decode($request->bearerToken(), new Key(config('forest.api.auth-secret'), 'HS256'));

                    return new ForestUser((array) $tokenData);
                }
            }
        );
    }
}
