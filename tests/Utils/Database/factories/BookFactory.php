<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class BookFactory
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class BookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Book::class;

    /**
     * @return array
     * @throws \JsonException
     */
    public function definition()
    {
        return [
            'label'             => $this->faker->randomElement(['foo', $this->faker->name()]),
            'comment'           => $this->faker->sentence(),
            'difficulty'        => $this->faker->randomElement(['easy', 'hard']),
            'amount'            => $this->faker->randomFloat(2),
            'active'            => $this->faker->boolean(),
            'options'           => [$this->faker->name() => $this->faker->name()],
            'category_id'       => Category::all()->random()->id,
        ];
    }
}
