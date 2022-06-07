<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyDissociator;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders\RelatedDataSeeder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Sequel;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Mockery as m;

/**
 * Class HasManyDissociatorTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyDissociatorTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RelatedDataSeeder::class);
    }

    /**
     * @return void
     */
    public function testRemoveRelationHasMany(): void
    {
        $book = Book::first();
        $movie = Movie::create(['body' => 'test movie', 'book_id' => $book->id]);

        $repository = m::mock(HasManyDissociator::class, [$book, 'movies', $book->id])
            ->makePartial();
        $data = $repository->removeRelation([$movie->id]);

        $this->assertNull($data);
    }

    /**
     * @return void
     */
    public function testRemoveRelationMorphMany(): void
    {
        $book = Book::first();
        $sequel = Sequel::create(['label' => 'test movie', 'sequelable_type' => Book::class, 'sequelable_id' => $book->id]);

        $repository = m::mock(HasManyDissociator::class, [$book, 'sequels', $book->id])
            ->makePartial();
        $data = $repository->removeRelation([$sequel->id]);

        $this->assertNull($data);
    }

    /**
     * @return void
     */
    public function testRemoveRelationBelongsToMany(): void
    {
        $book = Book::first();

        $repository = m::mock(HasManyDissociator::class, [$book, 'ranges', $book->id])
            ->makePartial();
        $data = $repository->removeRelation(Range::all()->pluck('id')->toArray());

        $this->assertNull($data);
    }

    /**
     * @return void
     */
    public function testRemoveRelationExceptionRecordNotFound(): void
    {
        $book = Book::first();
        $repository = m::mock(HasManyDissociator::class, [$book, 'movies', $book->id])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 Record dissociate error: records not found");

        $repository->removeRelation([100]);
    }

    /**
     * @return void
     */
    public function testRemoveRelationExceptionCannotDissociate(): void
    {
        $book = Book::first();

        $repository = m::mock(HasManyDissociator::class, [$book, 'comments', $book->id])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 Record dissociate error: the records can not be dissociate");

        $repository->removeRelation(Comment::pluck('id')->toArray());
    }

    /**
     * @return void
     */
    public function testRemoveRelationWithDelete(): void
    {
        $book = Book::first();

        $repository = m::mock(HasManyDissociator::class, [$book, 'comments', $book->id])
            ->makePartial();
        $data = $repository->removeRelation(Comment::pluck('id')->toArray(), true);

        $this->assertNull($data);
    }
}
