<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Transformers\BaseTransformer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;

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
     * @return array|null
     * @throws BindingResolutionException
     */
    public function render($class, string $name)
    {
        $this->fractal->setSerializer(new JsonApiSerializer(config('app.url')));
        $transformer = app()->make(BaseTransformer::class);

        if (is_array($class) || $this->isCollection($class)) {
            $resource = new Collection($class, $transformer, $name);
        } elseif ($this->isPaginator($class)) {
            $resource = new Collection($class->getCollection(), $transformer, $name);
            $resource->setPaginator(new IlluminatePaginatorAdapter($class));
        } else {
            $resource = new Item($class, $transformer, $name);
        }

        return $this->fractal->createData($resource)->toArray();
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
}
