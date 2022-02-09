<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;
use ForestAdmin\LaravelForestAdmin\Transformers\ChartTransformer;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Ramsey\Uuid\Uuid;

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
        $repository = new ('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\\' . $type)($model);

        return response()->json(
            JsonApi::renderItem(
                [
                    'id'    => Uuid::uuid4(),
                    'value' => $repository->get(),
                ],
                'stats',
                ChartTransformer::class
            )
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function liveQuery()
    {
        $type = request()->input('type');
        if (!$type || !in_array($type, ['Type', 'Value', 'Pie', 'Objective', 'Leaderboard'], true)) {
            throw new ForestException('The type of chart is not recognized.');
        }

        $repository = new ('\ForestAdmin\LaravelForestAdmin\Repositories\LiveQueries\\' . $type)();


        return response()->json(
            JsonApi::renderItem(
                [
                    'id'    => Uuid::uuid4(),
                    'value' => $repository->get(),
                ],
                'stats',
                ChartTransformer::class
            )
        );
    }
}
