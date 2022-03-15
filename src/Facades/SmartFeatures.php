<?php

namespace ForestAdmin\LaravelForestAdmin\Facades;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartFeaturesHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * Class SmartFeaturesHandler
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 *
 * @method static Model handleSmartFields(Model $model)
 *
 * @see SmartFeaturesHandler
 */
class SmartFeatures extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'smart-features';
    }
}
