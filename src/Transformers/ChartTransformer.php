<?php

namespace ForestAdmin\LaravelForestAdmin\Transformers;

use League\Fractal\TransformerAbstract;

/**
 * Class ChartTransformer
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartTransformer extends TransformerAbstract
{
    /**
     * @param array $data
     * @return array
     */
    public function transform(array $data)
    {
        return $data;
    }
}
