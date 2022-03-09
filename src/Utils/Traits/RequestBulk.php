<?php

namespace ForestAdmin\LaravelForestAdmin\Utils\Traits;

/**
 * Class RequestBulk
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait RequestBulk
{
    /**
     * @return array
     * @throws \Exception
     */
    public function getIdsFromBulkRequest(): array
    {
        $model = $this->getModel(ucfirst(request()->input('data.attributes.collection_name')));
        $request = request()->only('data.attributes.ids', 'data.attributes.all_records', 'data.attributes.all_records_ids_excluded');
        [$ids, $allRecords, $idsExcluded] = array_values($request['data']['attributes']);


        if ($allRecords) {
            return $model->whereNotIn($model->getKeyName(), $idsExcluded)->pluck('id')->toArray();
        } else {
            return $ids;
        }
    }
}
