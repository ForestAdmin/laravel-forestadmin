<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery;

use ForestAdmin\LaravelForestAdmin\Repositories\LiveQueryRepository;
use Illuminate\Support\Collection;

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
     * @param Collection $data
     * @return array
     */
    public function serialize(Collection $data): array
    {
        $this->abortIf(!isset($data->first()->value, $data->first()->objective), collect($data->first()), "'value', 'objective'");

        return [
            'value'     => $data->first()->value,
            'objective' => $data->first()->objective,
        ];
    }
}
