<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Auth\OidcConfiguration;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Htptp;

/**
 * Class ApiMapsController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ApiMapsController extends Controller
{
    /**
     * @return Response
     */
    public function index()
    {
        return response()->noContent();
    }
}
