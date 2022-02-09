<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;

/**
 * Class Objective
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Objective extends LiveQueryRepository
{
    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        $this->abortIf(!isset($data->first()->value, $data->first()->objective), $data, "'value', 'objective'");

        return [
            'value' => $data->first()->value,
            'objective' => $data->first()->objective,
        ];
    }
}
