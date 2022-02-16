<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;
use Illuminate\Support\Collection;

/**
 * Class Pie
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Pie extends LiveQueryRepository
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

        return $data->toArray();
    }
}
