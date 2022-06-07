<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\BelongsToUpdator;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders\RelatedDataSeeder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Advertisement;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Editor;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
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
    public function testUpdateRelationBelongsTo(): void
    {
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
        $book = Book::first();
        $book->editor->book_id = null;
        $book->editor->save();
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
    public function testUpdateRelationExceptionRecordNotFound(): void
    {
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
        $book = Book::first();
        $advertisementOfBook2 = Advertisement::firstWhere('book_id', 2);
        $repository = m::mock(BelongsToUpdator::class, [$book, 'advertisement', $book->id])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 The record can not be updated");

        $repository->updateRelation($advertisementOfBook2->id);
    }
}
