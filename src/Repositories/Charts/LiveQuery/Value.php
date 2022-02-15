<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;
use Illuminate\Support\Collection;

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
     * @param Collection $data
     * @return array
     */
    public function serialize(Collection $data): array
    {
        $this->abortIf(!isset($data->first()->value), collect($data->first()), "'value'");

        return [
            'countCurrent'  => $data->first()->value,
            'countPrevious' => null,
        ];
    }
}
