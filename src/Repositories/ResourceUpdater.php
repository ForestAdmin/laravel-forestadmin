<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ResourceUpdater
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceUpdater extends BaseRepository
{
    /**
     * @param $id
     * @return Model
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function update($id): Model
    {
        $model = $this->model::firstWhere($this->model->getKeyName(), $id);
        $this->setAttributes($model, request()->get('data'));

        try {
            $model->save();
        } catch (\Exception $e) {
            return $this->throwException('Record update error: ' . $e->getMessage());
        }

        $resourceGetter = new ResourceGetter($model, $this->name);

        return $resourceGetter->get($model->id);
    }
}