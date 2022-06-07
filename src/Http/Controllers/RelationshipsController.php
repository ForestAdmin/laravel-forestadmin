<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\BelongsToUpdator;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyAssociator;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyDissociator;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyGetter;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class RelationshipsController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RelationshipsController extends ForestController
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
     * @param $method
     * @param $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function callAction($method, $parameters)
    {
        $this->model = $this->getModel(ucfirst($parameters['collection']));
        $this->name = (class_basename($this->model));
        $this->relationship = $parameters['association_name'];
        $this->relationName = (class_basename($this->model->{$this->relationship}()->getRelated()));
        $this->parentId = $parameters['id'];

        return parent::callAction($method, $parameters);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $repository = new HasManyGetter($this->model, $this->relationship, $this->parentId);

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
        $repository = new HasManyGetter($this->model, $this->relationship, $this->parentId);

        return response()->json(['count' => $repository->count()]);
    }

    /**
     * @return Response
     */
    public function associate()
    {
        $repository = new HasManyAssociator($this->model, $this->relationship, $this->parentId);
        $repository->addRelation($this->getIds());

        return response()->noContent();
    }

    /**
     * @return JsonResponse|Response
     */
    public function dissociate()
    {
        try {
            $repository = new HasManyDissociator($this->model, $this->relationship, $this->parentId);
            $delete = request()->query('delete') ?? false;
            $repository->removeRelation($this->getIds(), $delete);
            return response()->noContent();
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return void
     */
    public function update()
    {
        try {
            $repository = new BelongsToUpdator($this->model, $this->relationship, $this->parentId);
            $repository->updateRelation(request()->input('data')['id']);

            return response()->noContent();
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return collect(request()->input('data'))->pluck('id')->toArray();
    }
}
