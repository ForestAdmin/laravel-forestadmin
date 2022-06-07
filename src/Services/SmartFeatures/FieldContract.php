<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

/**
 * Interface FieldContract
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
interface FieldContract
{
    /**
     * @return string
     */
    public function getField(): string;

    /**
     * @return array
     */
    public function serialize(): array;
}
