<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\BelongsToUpdator;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Advertisement;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Editor;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Image;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Mockery as m;

/**
 * Class BelongsToUpdatorTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BelongsToUpdatorTest extends TestCase
{
    use FakeData;

    /**
     * @return void
     */
    public function testUpdateRelationBelongsTo(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $newCategory = Category::create(['label' => 'new category']);

        $repository = m::mock(BelongsToUpdator::class, [$book, 'category', $book->id])
            ->makePartial();
        $data = $repository->updateRelation($newCategory->id);
        $book = $book->fresh();

        $this->assertNull($data);
        $this->assertEquals($newCategory->id, $book->category->id);
    }

    /**
     * @return void
     */
    public function testUpdateRelationHasTo(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $editor = Editor::create(['name' => 'John Doe', 'book_id' => 2]);

        $repository = m::mock(BelongsToUpdator::class, [$book, 'editor', $book->id])
            ->makePartial();
        $data = $repository->updateRelation($editor->id);
        $book = $book->fresh();

        $this->assertNull($data);
        $this->assertEquals($editor->id, $book->editor->id);
    }

    /**
     * @return void
     */
    public function testUpdateRelationMorphOne(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $image = Image::create(
            [
                'name'           => 'foo',
                'url'            => 'http://example.com',
                'imageable_type' => Book::class,
                'imageable_id'   => 2,
            ]
        );

        $repository = m::mock(BelongsToUpdator::class, [$book, 'image', $book->id])
            ->makePartial();
        $data = $repository->updateRelation($image->id);
        $book = $book->fresh();

        $this->assertNull($data);
        $this->assertEquals($image->id, $book->image->id);
    }

    /**
     * @return void
     */
    public function testUpdateRelationExceptionRecordNotFound(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        $repository = m::mock(BelongsToUpdator::class, [$book, 'category', $book->id])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 Record not found");

        $repository->updateRelation(100);
    }

    /**
     * @return void
     */
    public function testUpdateRelationExceptionCannotBeUpdated(): void
    {
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
            Advertisement::create(['label' => 'foo', 'book_id' => $i + 1]);
        }
        $book = Book::first();
        $advertisementOfBook2 = Advertisement::firstWhere('book_id', 2);

        $repository = m::mock(BelongsToUpdator::class, [$book, 'advertisement', $book->id])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The record can not be updated");

        $repository->updateRelation($advertisementOfBook2->id);
    }
}
