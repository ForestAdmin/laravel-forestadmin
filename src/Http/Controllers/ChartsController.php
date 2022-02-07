<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;

/**
 * Class ChartsController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartsController extends ForestController
{
    use Schema;

    public function index()
    {
        $name = request()->route()->parameter('collection');
        $model = Schema::getModel(ucfirst($name));


        dd(1);
    }


    public function liveQuery()
    {
        dd(2);
    }
}
