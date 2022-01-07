<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceRemover;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
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
    use FakeData;

    /**
     * @return void
     */
    public function testDestroy(): void
    {
        $this->getBook()->save();
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
        $this->getBook()->save();
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
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
        }
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
        for ($i = 0; $i < 5; $i++) {
            $this->getBook()->save();
        }
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
        $this->getBook()->save();
        $repository = m::mock(ResourceRemover::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Records destroy error: Collection nof found');

        $repository->destroyBulk(100);
    }
}
