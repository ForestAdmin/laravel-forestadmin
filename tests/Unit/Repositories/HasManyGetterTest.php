<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Repositories\HasManyGetter;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Tag;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery as m;

/**
 * Class HasManyGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyGetterTest extends TestCase
{
    use FakeData;

    /**
     * @return void
     */
    public function testAllWithHasMany(): void
    {
        $this->getBook()->save();
        $this->getComments();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'comments', 1])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Comment::first(), $data->items()[0]);
    }

    /**
     * @return void
     */
    public function testAllWithBelongsToManyRelation(): void
    {
        $this->getBook()->save();
        $this->getRanges();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'ranges', 1])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Range::first()->getAttributes(), $data->items()[0]->getAttributes());
    }

    /**
     * @return void
     */
    public function testAllWithMorphManyRelation(): void
    {
        $this->getBook()->save();
        $this->getTags();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'tags', 1])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Tag::first()->getAttributes(), $data->items()[0]->getAttributes());
    }

    /**
     * @return void
     */
    public function testCount(): void
    {
        $this->getBook()->save();
        $this->getComments();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'comments', 1])
            ->makePartial();

        $this->assertEquals(Comment::count(), $repository->count());
    }
}
