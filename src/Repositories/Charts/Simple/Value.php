<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;

/**
 * Class Value
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Value extends ChartRepository
{
    /**
     * @param $data
     * @return array
     */
    public function serialize($data): array
    {
        return [
            'countCurrent'  => $data,
            'countPrevious' => null,
        ];
    }
}
