<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple;

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
     * @param mixed $data
     * @return array
     */
    public function serialize($data): array
    {
        return [
            'value'  => $data,
        ];
    }
}
