<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Auth\OidcConfiguration;
use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class ApiMapsController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ApiMapsController extends ForestController
{
    /**
     * @return JsonResponse|Response
     * @throws BindingResolutionException
     */
    public function index()
    {
        if (config('forest.api.secret')) {
            app()->make(Schema::class)->sendApiMap();
            return response()->noContent();
        } else {
            return response()->json(['error' => 'forest secret is missing'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
