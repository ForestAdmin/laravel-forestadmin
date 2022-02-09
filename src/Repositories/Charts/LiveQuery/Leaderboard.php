<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;

/**
 * Class Leaderboard
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Leaderboard extends LiveQueryRepository
{
    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        $data->each(
            fn ($item) => $this->abortIf(!isset($item->value, $item->key), collect($item), "'key', 'value'")
        );

        return $data->toArray();
    }
}
