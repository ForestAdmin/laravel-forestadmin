<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\TestTransformer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
    use FakeData;

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

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $books = Book::select('books.id', 'books.label', 'books.comment', 'books.category_id', 'books.difficulty')
            ->with('category:categories.id')
            ->get();
        $render = $jsonApi->render($books, 'Book');

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data, $render['data'][0]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRenderPaginate(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->addDatabaseContent();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $books = Book::select('books.id', 'books.label', 'books.comment', 'books.category_id', 'books.difficulty')
            ->with('category:categories.id')
            ->paginate();
        $render = $jsonApi->render($books, 'Book');

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data, $render['data'][0]);
    }


    /**
     * @return void
     * @throws BindingResolutionException
     * @throws \JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRender(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->addDatabaseContent();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::select('books.id', 'books.label', 'books.comment', 'books.category_id', 'books.difficulty')
            ->with('category:categories.id')
            ->first();

        $render = $jsonApi->render($book, 'Book');
        $comments = [
            'links' => [
                'related' => [
                    'href' => '/forest/book/1/relationships/comments',
                ],
            ],
        ];

        //--- test smartRelationship HasMany ---//
        $smartBookstores = [
            "links" => [
                "related" => [
                    "href" => "/forest/book/1/relationships/smartBookstores",
                ],
            ],
        ];

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data['type'], $render['data']['type']);
        $this->assertEquals($data['id'], $render['data']['id']);
        $this->assertEquals($data['attributes'], $render['data']['attributes']);
        $this->assertEquals($data['relationships']['category'], $render['data']['relationships']['category']);
        $this->assertEquals($comments, $render['data']['relationships']['comments']);
        $this->assertEquals($smartBookstores, $render['data']['relationships']['smartBookstores']);
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws \JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRenderSmartBelongsTo(): void
    {
        $jsonApi = new JsonApiResponse();
        $this->addDatabaseContent();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $movie = Movie::first();
        $render = $jsonApi->render($movie, 'Movie');
        $included = [
            [
                'type'       => 'Category',
                'id'         => (string)$movie->book->category->id,
                'attributes' => [
                    'label'      => $movie->book->category->label,
                    'created_at' => $movie->book->category->created_at->jsonSerialize(),
                    'updated_at' => $movie->book->category->updated_at->jsonSerialize(),
                ],
            ],
        ];
        $attributes = [
            'body'       => $movie->body,
            'book_id'    => $movie->book_id,
            'created_at' => $movie->created_at->jsonSerialize(),
            'updated_at' => $movie->updated_at->jsonSerialize(),
        ];
        //--- test smartRelationship HasMany ---//
        $smartCategory = [
            'data' => [
                'type' => 'Category',
                'id'   => '1',
            ],
        ];

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($included, $render['included']);
        $this->assertEquals('Movie', $render['data']['type']);
        $this->assertEquals($movie->id, $render['data']['id']);
        $this->assertEquals($attributes, $render['data']['attributes']);
        $this->assertEquals($smartCategory, $render['data']['relationships']['smartCategory']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSearchDecorator(): void
    {
        $this->getBook()->save();
        $jsonApi = new JsonApiResponse();
        $books = Book::all();
        $decorators = $this->invokeMethod($jsonApi, 'searchDecorator', [$books, 'foo']);

        $this->assertIsArray($decorators);
        $this->assertArrayHasKey('decorators', $decorators);
        $this->assertEquals(['id' => 1, 'search' => ['label']], $decorators['decorators'][0]);
    }

    public function testRenderItem(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = [
            'id'  => 1,
            'foo' => 'bar',
        ];
        $renderItem = $jsonApi->renderItem($data, 'data', TestTransformer::class);

        $this->assertEquals([
            'data' => [
                'type'       => 'data',
                'id'         => '1',
                'attributes' => [
                    'foo' => 'bar',
                ],
            ]
        ], $renderItem);
    }

    /**
     * @return array
     */
    public function addDatabaseContent(): array
    {
        $category = Category::create(['label' => 'category1']);
        $book1 = Book::create(['label' => 'foo', 'comment' => 'test', 'difficulty' => 'easy', 'amount' => 100.00, 'options' => [], 'category_id' => 1]);
        $book1->setRelation('category', $category);
        $book1->movies()->saveMany([
            new Movie(['body' => 'foo body 1']),
            new Movie(['body' => 'foo body 2']),
        ]);
        $book2 = Book::create(['label' => 'bar', 'comment' => 'test', 'difficulty' => 'easy', 'amount' => 50.00, 'options' => [], 'category_id' => 1]);
        $book2->setRelation('category', $category);
        $book1->movies()->saveMany([
            new Movie(['body' => 'foo body 3']),
            new Movie(['body' => 'foo body 4']),
        ]);

        return [
            'type'          => 'Book',
            'id'            => (string)$book1->id,
            'attributes'    => [
                'label'       => $book1->label,
                'comment'     => $book1->comment,
                'difficulty'  => $book1->difficulty,
                'category_id' => (string)$category->id,
                'reference'   => call_user_func($book1->reference()->get),
            ],
            'relationships' => [
                'category'        => [
                    'data' => [
                        'type' => class_basename($category),
                        'id'   => (string)$category->id,
                    ],
                ],
                'comments'        => [
                    'links' => [
                        'related' => [
                            'href' => '/forest/book/1/relationships/comments',
                        ],
                    ],
                ],
                'smartBookstores' => [
                    'links' => [
                        'related' => [
                            'href' => '/forest/book/1/relationships/smartBookstores',
                        ],
                    ],
                ],
            ],
        ];
    }
}
