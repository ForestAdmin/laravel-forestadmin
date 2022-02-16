<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;
use Illuminate\Support\Collection;

/**
 * Class Line
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Line extends LiveQueryRepository
{
    /**
     * @param Collection $data
     * @return array
     */
    public function serialize(Collection $data): array
    {
        $data->each(
            fn ($item) => $this->abortIf(!isset($item->value, $item->key), collect($item), "'key', 'value'")
        );

        return $data->map(
            fn  ($item) => ['label' => $item->key, 'values' => ['value' => $item->value]]
        )->all();
    }
}
