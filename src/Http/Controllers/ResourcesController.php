<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceCreator;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceRemover;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceUpdater;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ResourcesController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourcesController extends Controller
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
     * @throws \Exception
     */
    public function __construct()
    {
        $collection = request()->route()->parameter('collection');
        $this->model = Schema::getModel(ucfirst($collection));
        $this->name = (class_basename($this->model));
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $repository = new ResourceGetter($this->model, $this->name);

        return response()->json(
            JsonApi::render($repository->all(), $this->name)
        );
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function show(): JsonResponse
    {
        $repository = new ResourceGetter($this->model, $this->name);

        try {
            $id = request()->route()->parameter('id');
            return response()->json(
                JsonApi::render($repository->get($id), $this->name)
            );
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return JsonResponse
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(): JsonResponse
    {
        try {
            $repository = new ResourceCreator($this->model, $this->name);
            return response()->json(
                JsonApi::render($repository->create(), $this->name),
                Response::HTTP_CREATED
            );
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @return JsonResponse
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(): JsonResponse
    {
        try {
            $repository = new ResourceUpdater($this->model, $this->name);
            $id = request()->input('data.' . $this->model->getKeyName());
            return response()->json(
                JsonApi::render($repository->update($id), $this->name),
                Response::HTTP_OK
            );
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        try {
            $id = request()->route()->parameter($this->model->getKeyName());
            $repository = new ResourceRemover($this->model, $this->name);
            return response()->json($repository->destroy($id), Response::HTTP_NO_CONTENT);
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return JsonResponse
     */
    public function count(): JsonResponse
    {
        $repository = new ResourceGetter($this->model, $this->name);

        return response()->json(['count' => $repository->count()]);
    }

    /**
     * @return JsonResponse
     */
    public function destroyBulk(): JsonResponse
    {
        try {
            $repository = new ResourceRemover($this->model, $this->name);
            $request = request()->only('data.attributes.ids', 'data.attributes.all_records', 'data.attributes.all_records_ids_excluded');
            [$ids, $allRecords, $idsExcluded] = array_values($request['data']['attributes']);
            return response()->json(
                $repository->destroyBulk($ids, $allRecords, $idsExcluded),
                Response::HTTP_NO_CONTENT
            );
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
