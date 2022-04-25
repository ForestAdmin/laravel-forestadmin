<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders;

use Faker\Factory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Advertisement;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Author;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Bookstore;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Company;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Editor;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Product;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Sequel;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Tag;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class RelatedDataSeeder
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class RelatedDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $users = User::factory(3)
            ->has(
                Product::factory()
                    ->count(3)
            )
            ->create();
        $ranges = Range::factory(20)->create();
        $faker = Factory::create();
        $books = Book::all()->each(
            function ($book) use ($faker, $ranges, $users) {
                $book->ranges()->attach(
                    $ranges->random(rand(1, 3))->pluck('id')->toArray()
                );
                $book->comments()->saveMany(
                    [
                    new Comment(['user_id' => $users->find(rand(1, 3))->id, 'body' => $faker->sentence()]),
                    new Comment(['user_id' => $users->find(rand(1, 3))->id, 'body' => $faker->sentence()]),
                    ]
                );
                $book->movies()->saveMany(
                    [
                    new Movie(['body' => $faker->sentence()]),
                    new Movie(['body' => $faker->sentence()]),
                    ]
                );

                for ($i = 0; $i < 2; $i++) {
                    $tag = new Tag(['label' => $faker->name]);
                    $tag->taggable()->associate($book);
                    $tag->save();

                    $sequel = new Sequel(['label' => $faker->name]);
                    $sequel->sequelable()->associate($book);
                    $sequel->save();
                }
            }
        );

        foreach ($books as $key => $book) {
            for ($i = 0; $i < 2; $i++) {
                $company = Company::create(['name' => $faker->name, 'book_id' => $book->id]);
                Bookstore::create(['label' => $faker->name, 'company_id' => $company->id]);
            }

            $user = $users->find(rand(1, 3));
            $author = Author::create(['book_id' => $book->id]);
            $user->author_id = $author->id;
            $user->save();

            Editor::create(['name' => $faker->name, 'book_id' => $book->id]);
            Advertisement::create(['label' => $faker->name, 'book_id' => $book->id]);
        }
    }
}
