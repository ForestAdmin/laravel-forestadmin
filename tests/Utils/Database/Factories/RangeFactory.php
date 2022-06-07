<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Range;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class RangeFactory
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class RangeFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Range::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'label' => $this->faker->name(),
        ];
    }
}
