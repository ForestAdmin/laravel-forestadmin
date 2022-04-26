<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders\RelatedDataSeeder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
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

    public function testDeactivateCountResponse()
    {
        $jsonApi = new JsonApiResponse();
        $response = $jsonApi->deactivateCountResponse();
        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['meta' => ['count' => 'deactivated']], $content);
    }

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
     * @throws \JsonException
     */
    public function testRenderCollection(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->expectedFormattedContent(Book::orderBy('id')->first());

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $books = Book::with('category:categories.id')->orderBy('id')->get();
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
    public function testRenderCollectionWithMeta(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->expectedFormattedContent(Book::orderBy('id')->first());
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $books = Book::with('category:categories.id')->orderBy('id')->get();
        $render = $jsonApi->render($books, 'Book', ['foo' => 'bar']);

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data, $render['data'][0]);
        $this->assertEquals(['foo' => 'bar'], $render['meta']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testRenderPaginate(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->expectedFormattedContent(Book::orderBy('id')->first());
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $books = Book::with('category:categories.id')->orderBy('id')->paginate();
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
    public function testRenderPaginateWithMeta(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->expectedFormattedContent(Book::orderBy('id')->first());
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $books = Book::with('category:categories.id')->orderBy('id')->paginate();
        $render = $jsonApi->render($books, 'Book', ['foo' => 'bar']);

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data, $render['data'][0]);
        $this->assertEquals(['foo' => 'bar'], $render['meta']);
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
        $data = $this->expectedFormattedContent(Book::orderBy('id')->first());
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::with('category:categories.id')->orderBy('id')->first();

        $render = $jsonApi->render($book, 'Book');
        $comments = [
            'links' => [
                'related' => [
                    'href' => '/forest/book/' . $book->id . '/relationships/comments',
                ],
            ],
        ];

        //--- test smartRelationship HasMany ---//
        $smartBookstores = [
            'links' => [
                'related' => [
                    'href' => '/forest/book/' . $book->id . '/relationships/smartBookstores',
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
    public function testRenderWithMeta(): void
    {
        $jsonApi = new JsonApiResponse();
        $data = $this->expectedFormattedContent(Book::orderBy('id')->first());
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $book = Book::with('category:categories.id')->orderBy('id')->first();
        $render = $jsonApi->render($book, 'Book', ['foo' => 'bar']);

        $this->assertIsArray($render);
        $this->assertArrayHasKey('data', $render);
        $this->assertArrayHasKey('included', $render);
        $this->assertEquals($data['type'], $render['data']['type']);
        $this->assertEquals($data['id'], $render['data']['id']);
        $this->assertEquals($data['attributes'], $render['data']['attributes']);
        $this->assertEquals($data['relationships']['category'], $render['data']['relationships']['category']);
        $this->assertEquals(['foo' => 'bar'], $render['meta']);
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
        $this->seed(RelatedDataSeeder::class);
        $jsonApi = new JsonApiResponse();
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
                'id'   => (string)$movie->book->category->id,
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
        $jsonApi = new JsonApiResponse();
        $books = Book::all();
        $book = $books->first();
        $decorators = $this->invokeMethod($jsonApi, 'searchDecorator', [$books, $book->label]);

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
     * @param Book $book
     * @return array
     */
    public function expectedFormattedContent(Book $book): array
    {
        return [
            'type'          => 'Book',
            'id'            => (string) $book->id,
            'attributes'    => [
                'label'        => $book->label,
                'comment'      => $book->comment,
                'difficulty'   => $book->difficulty,
                'amount'       => $book->amount,
                'active'       => $book->active,
                'options'      => $book->options,
                'other'        => $book->other,
                'category_id'  => (string) $book->category_id,
                'published_at' => $book->published_at,
                'sold_at'      => $book->sold_at,
                'created_at'   => $book->created_at->jsonSerialize(),
                'updated_at'   => $book->updated_at->jsonSerialize(),
                'reference'    => call_user_func($book->reference()->get)
            ],
            'relationships' => [
                'category'        => [
                    'data' => [
                        'type' => class_basename($book->category),
                        'id'   => (string) $book->category->id,
                    ],
                ],
                'comments'        => [
                    'links' => [
                        'related' => [
                            'href' => '/forest/book/' . $book->id . '/relationships/comments',
                        ],
                    ],
                ],
                'smartBookstores' => [
                    'links' => [
                        'related' => [
                            'href' => '/forest/book/' . $book->id . '/relationships/smartBookstores',
                        ],
                    ],
                ],
            ],
        ];
    }
}
