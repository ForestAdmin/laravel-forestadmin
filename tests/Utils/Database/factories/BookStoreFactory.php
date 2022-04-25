<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Bookstore;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class BookStoreFactory
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class BookStoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bookstore::class;

    /**
     * @return array
     * @throws \JsonException
     */
    public function definition()
    {
        return [
            'label'      => $this->faker->name(),
            'company_id' => Company::all()->random()->id,
        ];
    }
}
