<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use League\Fractal\TransformerAbstract;

/**
 * Class TestTransformer
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class TestTransformer extends TransformerAbstract
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
