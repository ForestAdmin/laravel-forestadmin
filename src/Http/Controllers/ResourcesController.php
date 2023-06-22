<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Exports\CollectionExport;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceCreator;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceRemover;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceUpdater;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laracsv\Export;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ResourcesController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourcesController extends ForestController
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
    protected string $requestFormat = 'json';

    /**
     * @param $method
     * @param $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function callAction($method, $parameters)
    {
        $this->name = $parameters['collection'];
        if (Str::endsWith($this->name, '.csv')) {
            $this->name = Str::replaceLast('.csv', '', $this->name);
            $this->requestFormat = 'csv';
        }
        $this->model = $this->getModel(ucfirst($this->name));

        return parent::callAction($method, $parameters);
    }

    /**
     * @return JsonResponse|Response
     * @throws AuthorizationException
     * @throws Exception
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function index()
    {
        $authorizeAction = $this->requestFormat === 'csv' ? 'export' : 'viewAny';
        $this->can($authorizeAction, $this->model);

        $repository = new ResourceGetter($this->model);

        if ($this->requestFormat === 'csv') {
            $filename = request()->input('filename', $this->name) . '.csv';
            $csvExporter = new Export();
            $export = $csvExporter->build(
                $repository->all(false),
                explode(',', request()->input('header'))
            )->getReader()->toString();

            return response(
                $export,
                200,
                [
                    'Content-type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]
            );
        } else {
            return response()->json(
                JsonApi::render($repository->all(), $this->name)
            );
        }
    }

    /**
     * @return JsonResponse
     * @throws Exception|AuthorizationException
     */
    public function show(): JsonResponse
    {
        $this->can('view', $this->model);

        $repository = new ResourceGetter($this->model);

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
     * @throws NotFoundExceptionInterface|AuthorizationException
     */
    public function store(): JsonResponse
    {
        $this->can('create', $this->model);

        try {
            $repository = new ResourceCreator($this->model);
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
     * @throws NotFoundExceptionInterface|AuthorizationException
     */
    public function update(): JsonResponse
    {
        $this->can('update', $this->model);

        try {
            $repository = new ResourceUpdater($this->model);
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
     * @throws AuthorizationException
     */
    public function destroy(): JsonResponse
    {
        $this->can('delete', $this->model);

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
     * @throws Exception
     * @throws AuthorizationException
     */
    public function count(): JsonResponse
    {
        $this->can('viewAny',  $this->model);

        $repository = new ResourceGetter($this->model);

        return response()->json(['count' => $repository->count()]);
    }

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroyBulk(): JsonResponse
    {
        $this->can('delete', $this->model);

        try {
            $repository = new ResourceRemover($this->model);
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
