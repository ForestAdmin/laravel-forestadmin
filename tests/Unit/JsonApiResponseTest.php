<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

/**
 * Class JsonApiResponseTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class JsonApiResponseTest extends TestCase
{
    use FakeSchema;

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testIsCollection(): void
    {
        $jsonApi = new JsonApiResponse();

        $collection = new Collection();
        $array = [];

        $this->assertTrue($this->invokeMethod($jsonApi, 'isCollection', [$collection]));
        $this->assertFalse($this->invokeMethod($jsonApi, 'isCollection', [$array]));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testIsPaginator(): void
    {
        $jsonApi = new JsonApiResponse();

        $booksPaginate = Book::paginate();
        $books = Book::all();

        $this->assertTrue($this->invokeMethod($jsonApi, 'isPaginator', [$booksPaginate]));
        $this->assertFalse($this->invokeMethod($jsonApi, 'isPaginator', [$books]));
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRenderCollection(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->addDatabaseContent();

        $books = Book::select('books.id', 'books.label', 'books.comment', 'books.category_id')
            ->with('category:categories.id')
            ->get();
        $render = $jsonApi->render($books, 'Book');

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertSame($data, $render['data'][0]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRenderPaginate(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->addDatabaseContent();

        $books = Book::select('books.id', 'books.label', 'books.comment', 'books.category_id')
            ->with('category:categories.id')
            ->paginate();
        $render = $jsonApi->render($books, 'Book');

        $meta = [
            'pagination' => [
                'total'        => 2,
                'count'        => 2,
                'per_page'     => 15,
                'current_page' => 1,
                'total_pages'  => 1,
            ],
        ];

        $links = [
            'self'  => 'http://localhost?page=1',
            'first' => 'http://localhost?page=1',
            'last'  => 'http://localhost?page=1',
        ];


        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertArrayHasKey('meta', $render);
        $this->assertArrayHasKey('links', $render);
        $this->assertSame($data, $render['data'][0]);
        $this->assertSame($meta, $render['meta']);
        $this->assertSame($links, $render['links']);
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRenderItem(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->addDatabaseContent();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::select('books.id', 'books.label', 'books.comment', 'books.category_id')
            ->with('category:categories.id')
            ->first();

        $render = $jsonApi->render($book, 'Book');
        $comments = [
            'links' => [
                'related' => [
                    'href' => '/forest/book/1/relationships/comments'
                ]
            ]
        ];

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data['type'], $render['data']['type']);
        $this->assertEquals($data['id'], $render['data']['id']);
        $this->assertEquals($data['attributes'], $render['data']['attributes']);
        $this->assertEquals($data['links'], $render['data']['links']);
        $this->assertEquals($data['relationships']['category'], $render['data']['relationships']['category']);
        $this->assertEquals($comments, $render['data']['relationships']['comments']);
    }

    /**
     * @return array
     */
    public function addDatabaseContent(): array
    {
        $category = Category::create(['label' => 'category1', 'product_id' => 1]);
        $book1 = Book::create(['label' => 'foo', 'comment' => 'test', 'difficulty' => 'easy', 'amount' => 100.00, 'options' => [], 'category_id' => 1]);
        $book1->setRelation('category', $category);
        $book2 = Book::create(['label' => 'bar', 'comment' => 'test', 'difficulty' => 'easy', 'amount' => 50.00, 'options' => [], 'category_id' => 1]);
        $book2->setRelation('category', $category);

        return [
            'type'          => 'Book',
            'id'            => (string)$book1->id,
            'attributes'    => [
                'label'       => $book1->label,
                'comment'     => $book1->comment,
                'category_id' => (string)$category->id,
            ],
            'links'         => [
                'self' => 'http://localhost/Book/' . $book1->id,
            ],
            'relationships' => [
                'category' => [
                    'data' => [
                        'type' => class_basename($category),
                        'id'   => (string)$category->id,
                    ],
                ]
            ],
        ];
    }
}
