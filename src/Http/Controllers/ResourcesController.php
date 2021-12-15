<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceCreator;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
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
     * @throws \Exception
     */
    public function __construct()
    {
        $collection = request()->route()->parameter('collection');
        $this->model = Schema::getModel(ucfirst($collection));
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        try {
            $repository = new ResourceGetter($this->model);
            return response()->json($repository->all());
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function show(): JsonResponse
    {
        try {
            $id = request()->route()->parameter('id');
            $repository = new ResourceGetter($this->model);

            return response()->json($repository->get($id));
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
            $repository = new ResourceCreator($this->model);
            return response()->json($repository->create(), Response::HTTP_CREATED);
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
            $repository = new ResourceUpdater($this->model);
            $id = request()->input('data.' . $this->model->getKeyName());
            return response()->json($repository->update($id), Response::HTTP_CREATED);
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
            $repository = new ResourceRemover($this->model);
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
        $repository = new ResourceGetter($this->model);

        return response()->json(['count' => $repository->count()]);
    }

    /**
     * @return JsonResponse
     */
    public function destroyBulk(): JsonResponse
    {
        try {
            $repository = new ResourceRemover($this->model);
            $ids = request()->input('data.attributes.ids');
            return response()->json($repository->destroy($ids), Response::HTTP_NO_CONTENT);
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
