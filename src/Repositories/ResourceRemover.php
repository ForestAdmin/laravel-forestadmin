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
        $record = $this->model->destroy($id);

        if (0 === $record) {
            return $this->throwException('Record destroy error: Collection nof found');
        }
    }

    /**
     * @param Collection|array $ids
     * @param bool             $allRecords
     * @param array            $idsExcluded
     * @return void
     */
    public function destroyBulk($ids, bool $allRecords = false, array $idsExcluded = [])
    {
        if ($allRecords) {
            $record = $this->model->whereNotIn($this->model->getKeyName(), $idsExcluded)->delete();
        } else {
            $record = $this->model->destroy($ids);
        }

        if (0 === $record) {
            return $this->throwException('Records destroy error: Collection nof found');
        }
    }
}
