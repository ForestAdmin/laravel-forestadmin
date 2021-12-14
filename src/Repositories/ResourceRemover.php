<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Illuminate\Support\Collection;

/**
 * Class ResourceRemover
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceRemover extends BaseRepository
{
    /**
     * @param Collection|array|int|string $id
     * @return void
     */
    public function destroy($id)
    {
        $destroy = $this->model->destroy($id);

        if (0 === $destroy) {
            return $this->throwException('Record destroy error: Collection nof found');
        }
    }
}
