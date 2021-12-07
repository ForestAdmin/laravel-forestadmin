<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Repositories\BaseRepository;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
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
     * @var Model
     */
    protected Model $model;

    /**
     * @var BaseRepository
     */
    protected BaseRepository $baseRepository;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $collection = request()->route()->parameter('collection');
        $this->model = Schema::getModel(ucfirst($collection));
        $this->baseRepository = new BaseRepository($this->model);
    }

    /**
     * @return array
     */
    public function index()
    {
        return $this->baseRepository->all();
    }

    /**
     * @return JsonResponse
     */
    public function count()
    {
        $count = $this->baseRepository->count();

        return response()->json(compact('count'));
    }
}
