<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceCreator;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
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
     * @return array
     * @throws Exception
     */
    public function index(): array
    {
        $repository = new ResourceGetter($this->model);

        return $repository->all();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function show(): array
    {
        $id = request()->route()->parameter('id');
        $repository = new ResourceGetter($this->model);

        return $repository->get($id);
    }

    /**
     * @return array
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(): array
    {
        $repository = new ResourceCreator($this->model);

        return $repository->create();
    }

    /**
     * @return JsonResponse
     */
    public function count(): array
    {
        $repository = new ResourceGetter($this->model);
        $count = $repository->count();

        return response()->json(compact('count'));
    }
}
