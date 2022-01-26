<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class HasFiltersDateOperatorsTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasFiltersDateOperatorsTest extends TestCase
{
    /**
     * @param array|null $types
     * @return array
     */
    protected function getData(?array $types = null): array
    {
        $collection = collect(
            [
                [
                    'type'  => 'Date',
                    'field' => 'created_at',
                    'value' => '2022-01-01 12:00:00',
                ],
                [
                    'type'  => 'Dateonly',
                    'field' => 'published_at',
                    'value' => '2022-01-01',
                ],
                [
                    'type'  => 'Time',
                    'field' => 'delivery_hour',
                    'value' => '12:00:00',
                ],
            ]
        );

        return $collection->reject(fn($value, $key) => $types && !in_array($value['type'], $types))->all();
    }
}
