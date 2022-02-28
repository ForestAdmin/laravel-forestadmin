<?php

namespace ForestAdmin\LaravelForestAdmin\Facades;

use ForestAdmin\LaravelForestAdmin\Services\SmartActions\SmartActionService;
use Illuminate\Support\Facades\Facade;

/**
 * Class SmartActionService
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 *
 * @method static self create(string $name, string $endpoint, array $fields = [], string $type = 'bulk', bool $download = false)
 * @method static array serialize(array $data);
 *
 * @see SmartActionService
 */
class SmartAction extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'smart-action';
    }
}
