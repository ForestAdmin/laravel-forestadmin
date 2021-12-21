<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyAssociator;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyGetter;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
     * @var Model
     */
    protected Model $model;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    private string $relationship;

    /**
     * @var string
     */
    protected string $relationName;

    /**
     * @var string
     */
    protected string $parentId;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        [$collection, $parentId, $relation] = array_values(request()->route()->parameters());
        $this->model = Schema::getModel(ucfirst($collection));
        $this->name = (class_basename($this->model));
        $this->relationship = $relation;
        $this->relationName = (class_basename($this->model->$relation()->getRelated()));
        $this->parentId = $parentId;
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $repository = new HasManyGetter($this->model, $this->name, $this->relationship, $this->parentId);

        return response()->json(
            JsonApi::render($repository->all(), $this->relationName)
        );
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function count(): JsonResponse
    {
        $repository = new HasManyGetter($this->model, $this->name, $this->relationship, $this->parentId);

        return response()->json(['count' => $repository->count()]);
    }

    /**
     * @return JsonResponse
     */
    public function associate(): JsonResponse
    {
        $repository = new HasManyAssociator($this->model, $this->name, $this->relationship, $this->parentId);
        $ids = collect(request()->input('data'))->pluck('id')->toArray();
        $repository->addRelation($ids);

        return response()->noContent();
    }
}
