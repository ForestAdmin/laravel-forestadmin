<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ProductFactory
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'label' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'token' => $this->faker->uuid,
        ];
    }
}
