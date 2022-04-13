<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Serializer\JsonApiSerializer;
use ForestAdmin\LaravelForestAdmin\Transformers\BaseTransformer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class JsonApiResponse
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class JsonApiResponse
{
    /**
     * @var Manager
     */
    protected Manager $fractal;

    public function __construct()
    {
        $this->fractal = app()->make(Manager::class);
    }

    /**
     * @param        $class
     * @param string $name
     * @param array  $meta
     * @return array|null
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function render($class, string $name, array $meta = [])
    {
        $this->fractal->setSerializer(new JsonApiSerializer(config('app.url')));
        $transformer = app()->make(BaseTransformer::class);
        $transformer->setAvailableIncludes(ForestSchema::getRelatedData($name));

        if (is_array($class) || $this->isCollection($class)) {
            $resource = new Collection($class, $transformer, $name);
        } elseif ($this->isPaginator($class)) {
            $resource = new Collection($class->getCollection(), $transformer, $name);
            if (request()->has('search')) {
                $resource->setMeta($this->searchDecorator($resource->getData(), request()->get('search')));
            }
        } else {
            $resource = new Item($class, $transformer, $name);
        }

        if ($meta) {
            $resource->setMeta(array_merge($resource->getMeta(), $meta));
        }

        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * @param        $data
     * @param string $name
     * @param string $transformer
     * @return array|null
     * @throws BindingResolutionException
     */
    public function renderItem($data, string $name, string $transformer)
    {
        $this->fractal->setSerializer(new JsonApiSerializer(config('app.url')));
        $transformer = app()->make($transformer);
        $resource = new Item($data, $transformer, $name);

        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * @return JsonResponse
     */
    public function deactivateCountResponse(): JsonResponse
    {
        return response()->json(
            [
                'meta' => [
                    'count' => 'deactivated'
                ],
            ]
        );
    }

    /**
     * @param object $instance
     * @return bool
     */
    protected function isCollection($instance): bool
    {
        return $instance instanceof BaseCollection;
    }

    protected function isPaginator($instance): bool
    {
        return $instance instanceof LengthAwarePaginator;
    }

    /**
     * @param BaseCollection $items
     * @param mixed          $searchValue
     * @return array
     */
    protected function searchDecorator(BaseCollection $items, $searchValue): array
    {
        $decorator = ['decorators' => []];
        foreach ($items as $key => $value) {
            $decorator['decorators'][$key]['id'] = $value->getKey();
            foreach ($value->getAttributes() as $fieldKey => $fieldValue) {
                if (Str::contains(Str::lower($fieldValue), Str::lower($searchValue))) {
                    $decorator['decorators'][$key]['search'][] = $fieldKey;
                }
            }
        }

        return $decorator;
    }
}
