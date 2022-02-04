<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exports\CollectionExport;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CollectionExportTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class CollectionExportTest extends TestCase
{
    use FakeData;

    /**
     * @return void
     */
    public function testCollection(): void
    {
        $this->getBook()->save();
        $books = Book::get();
        $export = new CollectionExport($books, 'books', 'id,label');

        $this->assertInstanceOf(Collection::class, $export->collection());
        $this->assertEquals($books, $export->collection());
    }

    /**
     * @return void
     */
    public function testHeadings(): void
    {
        $this->getBook()->save();
        $books = Book::get();
        $export = new CollectionExport($books, 'books', 'id,label');

        $this->assertIsArray($export->headings());
        $this->assertEquals(['id', 'label'], $export->headings());
    }
}
