<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;

/**
 * Class Value
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Value extends LiveQueryRepository
{
    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        $this->abortIf(!isset($data->first()->value), collect($data->first()), "'value'");

        return [
            'countCurrent'  => $data->first()->value,
            'countPrevious' => null,
        ];
    }
}
