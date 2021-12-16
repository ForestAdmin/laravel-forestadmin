<?php

namespace ForestAdmin\LaravelForestAdmin\Facades;

use ForestAdmin\LaravelForestAdmin\Services\ForestSchemaInstrospection;
use Illuminate\Support\Facades\Facade;
use JsonPath\JsonObject;

/**
 * Class ForestSchema
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 *
 * @method static JsonObject getSchema()
 * @method static array|bool getRelatedData(string $collection)
 *
 * @see ForestSchemaInstrospection
 */
class ForestSchema extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'forest-schema';
    }
}
