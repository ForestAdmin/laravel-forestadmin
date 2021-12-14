<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
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
        $model = new $this->model();
        $this->setAttributes($model, request()->get('data'));

        try {
            $model->save();
        } catch (\Exception $e) {
            return $this->throwException('Record create error: ' . $e->getMessage());
        }

        $resourceGetter = new ResourceGetter($model);

        return $resourceGetter->get($model->id);
    }
}
