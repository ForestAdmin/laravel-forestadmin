<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Model;
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
     * @return Model
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function create(): Model
    {
        $model = new $this->model();
        $this->setAttributes($model, request()->get('data'));

        try {
            $model->save();
        } catch (\Exception $e) {
            return $this->throwException('Record create error: ' . $e->getMessage());
        }

        $resourceGetter = new ResourceGetter($model, $this->name);

        return $resourceGetter->get($model->id);
    }
}
