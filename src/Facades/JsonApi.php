<?php

namespace ForestAdmin\LaravelForestAdmin\Facades;

use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;

/**
 * Class JsonApi
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 *
 * @method static array render($class, string $name, array $metadata = [])
 * @method static array renderItem($class, string $name, string $transformer)
 * @method static JsonResponse deactivateCountResponse()
 *
 * @see JsonApiResponse
 */
class JsonApi extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'json-api';
    }
}
