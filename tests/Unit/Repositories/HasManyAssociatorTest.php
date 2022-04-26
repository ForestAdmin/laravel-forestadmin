<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Repositories\HasManyAssociator;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Sequel;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Mockery as m;

/**
 * Class HasManyAssociatorTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyAssociatorTest extends TestCase
{
    /**
     * @return void
     */
    public function testAddRelationHasMany(): void
    {
        $movie = Movie::create(['body' => 'test movie']);
        $book = Book::first();

        $repository = m::mock(HasManyAssociator::class, [$book, 'movies', $book->id])
            ->makePartial();
        $data = $repository->addRelation([$movie->id]);

        $this->assertNull($data);
        $this->assertEquals($movie->id, $book->movies->first()->pluck('id')->first());
    }

    /**
     * @return void
     */
    public function testAddRelationMorphMany(): void
    {
        $sequel = Sequel::create(['label' => 'sequel test']);
        $book = Book::first();

        $repository = m::mock(HasManyAssociator::class, [$book, 'sequels', $book->id])
            ->makePartial();
        $data = $repository->addRelation([$sequel->id]);

        $this->assertNull($data);
        $this->assertEquals($sequel->id, $book->sequels->first()->pluck('id')->first());
    }

    /**
     * @return void
     */
    public function testAddRelationBelongsToMany(): void
    {
        $books = Book::all();
        $book1 = $books->first();
        $book2 = $books->last();

        $range = new Range();
        $range->label = 'Test range';
        $book2->ranges()->save($range);

        $repository = m::mock(HasManyAssociator::class, [$book1, 'ranges', $book1->id])
            ->makePartial();
        $data = $repository->addRelation([$range->id]);

        $this->assertNull($data);
        $this->assertEquals($range->id, $book1->ranges->first()->pluck('id')->first());
    }
}
