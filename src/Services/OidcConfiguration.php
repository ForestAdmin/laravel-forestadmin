<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Utils\ForestApiRequester;
use Illuminate\Http\Client\Response;

/**
 * Class Oidc
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class OidcConfiguration
{
    /**
     * @return Response
     */
    public function retrieve(): Response
    {
        $forestApi = new ForestApiRequester();
        return $forestApi->post('/oidc/.well-known/openid-configuration');
    }
}
