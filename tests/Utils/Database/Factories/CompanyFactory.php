<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class CompanyFactory
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * @return array
     * @throws \JsonException
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->name(),
            'book_id'   => Book::all()->random()->id,
        ];
    }
}
