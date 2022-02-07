<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts;

use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;

/**
 * Class Objective
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Objective extends ChartRepository
{
    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        return [
            'value'  => $data,
        ];
    }

}
