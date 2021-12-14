<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ResourceCreator
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceCreator extends BaseRepository
{
    /**
     * @return array
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): array
    {
        $data = request()->get('data');
        $attributes = $data['attributes'];
        $relationships = $data['relationships'] ?? [];
        $model = new $this->model();

        foreach ($attributes as $key => $value) {
            $model->$key = $value;
        }

        foreach ($relationships as $key => $value) {
            $relation = $model->$key();
            $attributes = $value['data'];
            if ($relation instanceof BelongsTo && array_key_exists($relation->getOwnerKeyName(), $attributes)) {
                $related = $relation->getRelated()->firstWhere($relation->getOwnerKeyName(), $attributes[$relation->getOwnerKeyName()]);
                $model->$key()->associate($related);
            }
        }

        $model->save();
        $resourceGetter = new ResourceGetter($model);

        return $resourceGetter->get($model->id);
    }
}
