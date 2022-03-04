<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Category::factory(3)->create();
        Book::factory(10)->create();
    }
}
