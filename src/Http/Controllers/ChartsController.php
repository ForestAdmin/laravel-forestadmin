<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
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

    /**
     * @return void
     * @throws \Exception
     */
    public function index()
    {
        $type = request()->input('type');
        if (!$type || !in_array($type, ['Type', 'Value', 'Pie', 'Objective', 'Leaderboard'], true)) {
            throw new ForestException('The type of chart is not recognized.');
        }

        $name = request()->route()->parameter('collection');
        $model = Schema::getModel(ucfirst($name));
        $repository = new ('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\\' . $type)($model);

        dd($repository);
    }


    public function liveQuery()
    {
        dd(2);
    }
}
