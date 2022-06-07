<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceRemover;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Mockery as m;

/**
 * Class ResourceRemoverTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceRemoverTest extends TestCase
{
    /**
     * @return void
     */
    public function testDestroy(): void
    {
        $book = Book::first();
        $repository = m::mock(ResourceRemover::class, [$book, 'Book'])
            ->makePartial();

        $data = $repository->destroy($book->id);

        $this->assertNull($data);
    }

    /**
     * @return void
     */
    public function testDestroyException(): void
    {
        $repository = m::mock(ResourceRemover::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Record destroy error: Collection nof found');

        $repository->destroy(100);
    }

    /**
     * @return void
     */
    public function testDestroyBulk(): void
    {
        $bookIds = Book::pluck('id')->toArray();
        $repository = m::mock(ResourceRemover::class, [Book::first(), 'Book'])
            ->makePartial();
        $data = $repository->destroyBulk($bookIds);

        $this->assertNull($data);
    }

    /**
     * @return void
     */
    public function testDestroyBulkWithIdsExcluded(): void
    {
        $bookIds = Book::where('id', '<=', 5)->pluck('id')->toArray();
        $bookIdsExcluded = Book::where('id', '>', 5)->pluck('id')->toArray();
        $repository = m::mock(ResourceRemover::class, [Book::first(), 'Book'])
            ->makePartial();
        $data = $repository->destroyBulk($bookIds, true, $bookIdsExcluded);

        $this->assertNull($data);
    }

    /**
     * @return void
     */
    public function testDestroyBulkException(): void
    {
        $repository = m::mock(ResourceRemover::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Records destroy error: Collection nof found');

        $repository->destroyBulk(100);
    }
}
