<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Transformers\ChartTransformer;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Http\JsonResponse;
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
     * @throws \Exception
     */
    public function index(): JsonResponse
    {
        $name = request()->route()->parameter('collection');
        $model = Schema::getModel(ucfirst($name));
        $repository = new ('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\\' . $this->type)($model);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function liveQuery(): JsonResponse
    {
        $this->authorize('liveQuery', [request()->input('query')]);
        $repository = new ('\ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\\' . $this->type)();

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
