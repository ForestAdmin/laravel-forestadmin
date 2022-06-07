<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Transformers\ChildTransformer;
use Illuminate\Support\Facades\File;

/**
 * Class ChildTransformerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChildTransformerTest extends TestCase
{
    use FakeSchema;

    /**
     * @return void
     * @throws \JsonException
     */
    public function testTransform(): void
    {
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $book = new Book();
        $book->id = 1;
        $book->label = 'foo';
        $book->comment = 'test value';
        $book->difficulty = 'easy';
        $book->amount = 50.20;

        $transformer = new ChildTransformer();
        $transform = $transformer->transform($book);

        $this->assertEquals($book->attributesToArray(), $transform);
    }
}
