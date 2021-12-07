<?php

namespace ForestAdmin\LaravelForestAdmin\Utils\Traits;

/**
 * Class ArrayHelper
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait ArrayHelper
{
    /**
     * @param array $array
     * @param mixed $value
     * @return array
     */
    protected function mergeArray(array $array, $value): array
    {
        if (!is_array($value) && !in_array($value, $array, true)) {
            $array[] = $value;
        } elseif (is_array($value)) {
            foreach ($value as $v) {
                $array = $this->mergeArray($array, $v);
            }
        }

        return $array;
    }
}
