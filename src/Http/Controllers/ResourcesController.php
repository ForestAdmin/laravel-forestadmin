<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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
     * @var ResourceGetter
     */
    protected ResourceGetter $repository;

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
        $model = Schema::getModel(ucfirst($collection));
        $this->name = (class_basename($model));
        $this->repository = new ResourceGetter($model, $this->name);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        return response()->json(
            JsonApi::render($this->repository->all(), $this->name)
        );
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function show(): JsonResponse
    {
        try {
            $id = request()->route()->parameter('id');
            return response()->json(
                JsonApi::render($this->repository->get($id), $this->name)
            );
        } catch (ForestException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return JsonResponse
     */
    public function count(): JsonResponse
    {
        return response()->json(['count' => $this->repository->count()]);
    }
}
