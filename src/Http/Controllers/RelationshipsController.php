<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyGetter;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Class RelationshipsController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RelationshipsController extends Controller
{
    use Schema;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $relationName;

    /**
     * @var HasManyGetter
     */
    private HasManyGetter $repository;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        [$collection, $parentId, $relation] = array_values(request()->route()->parameters());
        $model = Schema::getModel(ucfirst($collection));
        $this->name = (class_basename($model));
        $this->relationName = (class_basename($model->$relation()->getRelated()));
        $this->repository = new HasManyGetter($model, $this->name, $relation, $this->relationName, $parentId);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        return response()->json(
            JsonApi::render($this->repository->all(), $this->relationName)
        );
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function count(): JsonResponse
    {
        return response()->json(['count' => $this->repository->count()]);
    }
}
