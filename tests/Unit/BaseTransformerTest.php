<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Transformers\BaseTransformer;
use Illuminate\Support\Facades\File;
use League\Fractal\Resource\Item;

/**
 * Class BaseTransformerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BaseTransformerTest extends TestCase
{
    use FakeSchema;

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAddMethod(): void
    {
        $transformer = new BaseTransformer();
        $this->invokeMethod($transformer, 'addMethod', ['foo', fn() => 'bar']);

        $this->assertTrue(property_exists($transformer, 'foo'));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testCall(): void
    {
        $transformer = new BaseTransformer();
        $this->invokeMethod($transformer, 'addMethod', ['foo', fn() => 'method called !']);

        $this->assertEquals('method called !', $transformer->foo());
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testTransform(): void
    {
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $category = new Category();
        $category->id = 1;
        $category->label = 'bar';

        $book = new Book();
        $book->id = 1;
        $book->label = 'foo';
        $book->comment = 'test value';
        $book->difficulty = 'easy';
        $book->amount = 50.20;
        $book->setRelation('category', $category);

        $transformer = new BaseTransformer();
        $transform = $transformer->transform($book);

        $this->assertEquals('category', $transformer->getDefaultIncludes()[0]);
        $this->assertTrue(property_exists($transformer, 'includeCategory'));
        $this->assertInstanceOf(Item::class, $transformer->includeCategory());
        $this->assertEquals($category, $transformer->includeCategory()->getData());
        $this->assertEquals($book->attributesToArray(), $transform);
    }
}
