<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Transformers\ChildTransformer;

/**
 * Class ChildTransformerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChildTransformerTest extends TestCase
{
    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testTransform(): void
    {
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
