<?php

namespace ForestAdmin\LaravelForestAdmin\Facades;

use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use Illuminate\Support\Facades\Facade;

/**
 * Class JsonApi
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 *
 * @method static array render($class, string $name)
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
