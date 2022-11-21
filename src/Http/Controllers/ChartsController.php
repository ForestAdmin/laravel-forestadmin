<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Transformers\ChartTransformer;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
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
     * @var string
     */
    protected string $type;

    /**
     * ChartsController constructor
     */
    public function __construct()
    {
        $this->validateRequestType();
        $this->type = request()->input('type');
    }

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->can('simpleCharts', request()->except('timezone'));

        $name = request()->route()->parameter('collection');
        $model = $this->getModel(ucfirst($name));
        $repository = App::makeWith('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\\' . $this->type, ['model' => $model]);

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
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function liveQuery(): JsonResponse
    {
        $this->can('liveQuery', request()->input('query'));
        $repository = App::make('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $this->type);

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
     */
    protected function validateRequestType(): void
    {
        if (!request()->input('type') || !in_array(request()->input('type'), ['Type', 'Value', 'Pie', 'Objective', 'Leaderboard', 'Line'], true)) {
            throw new ForestException("The chart's type is not recognized.");
        }
    }
}
