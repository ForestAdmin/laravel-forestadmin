<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyDissociator;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Sequel;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
    use FakeData;

    /**
     * @return void
     */
    public function testRemoveRelationHasMany(): void
    {
        $this->getBook()->save();
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
        $this->getBook()->save();
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
        $this->getBook()->save();
        $this->getRanges();
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
        $this->getBook()->save();
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
        $this->getBook()->save();
        $this->getComments();
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
        $this->getBook()->save();
        $this->getComments();
        $book = Book::first();

        $repository = m::mock(HasManyDissociator::class, [$book, 'comments', $book->id])
            ->makePartial();
        $data = $repository->removeRelation(Comment::pluck('id')->toArray(), true);

        $this->assertNull($data);
    }
}
