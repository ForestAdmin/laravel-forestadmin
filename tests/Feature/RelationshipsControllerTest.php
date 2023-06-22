<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Seeders\RelatedDataSeeder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Advertisement;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Company;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Editor;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Sequel;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Tag;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Bookstore;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockIpWhitelist;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

/**
 * Class RelationshipsControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RelationshipsControllerTest extends TestCase
{
    use FakeSchema;
    use MockForestUserFactory;
    use ScopeManagerFactory;
    use MockIpWhitelist;

    /**
     * @return void
     * @throws \JsonException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->seed(RelatedDataSeeder::class);

        $forestUser = new ForestUser(
            [
                'id'               => 1,
                'email'            => 'john.doe@forestadmin.com',
                'first_name'       => 'John',
                'last_name'        => 'Doe',
                'rendering_id'     => 1,
                'tags'             => [],
                'teams'            => 'Operations',
                'exp'              => 1643825269,
                'permission_level' => 'admin',
            ]
        );

        $forestResourceOwner = new ForestResourceOwner(
            array_merge(
                [
                    'type'                              => 'users',
                    'two_factor_authentication_enabled' => false,
                    'two_factor_authentication_active'  => false,
                ],
                $forestUser->getAttributes()
            ),
            $forestUser->getAttribute('rendering_id')
        );

        $this->withHeader('Authorization', 'Bearer ' . $forestResourceOwner->makeJwt());
        $this->mockForestUserFactory();
        $this->makeScopeManager($forestUser);
        $this->mockIpWhitelist();

        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndex(): void
    {
        App::shouldReceive('basePath')->andReturn(null);

        $params = ['fields' => ['comment' => 'id,body']];
        $call = $this->get('/forest/book/1/relationships/comments?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $comment = Comment::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('Comment', $data['data'][0]['type']);
        $this->assertEquals($comment->id, $data['data'][0]['id']);
        $this->assertEquals($comment->body, $data['data'][0]['attributes']['body']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndexSmartRelationship(): void
    {
        Company::factory(1)->create();
        Bookstore::factory(1)->create();

        App::shouldReceive('basePath')->andReturn(null);

        $call = $this->get('/forest/book/1/relationships/smartBookstores');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $bookstore = Bookstore::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('bookstore', $data['data'][0]['type']);
        $this->assertEquals($bookstore->id, $data['data'][0]['id']);
        $this->assertEquals($bookstore->label, $data['data'][0]['attributes']['label']);
        $this->assertEquals($bookstore->company_id, $data['data'][0]['attributes']['company_id']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testCount(): void
    {
        $bookId = 1;
        $call = $this->get("/forest/book/$bookId/relationships/comments/count");
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Comment::where('book_id', $bookId)->count(), $data['count']);
    }

    /**
     * @return void
     */
    public function testAssociateHasMany(): void
    {
        $book = Book::first();
        $comment = Comment::where('book_id', '!=', $book->id)->first();
        $call = $this->post(
            '/forest/book/' . $book->id . '/relationships/comments',
            [
                'data' => [
                    [
                        'id'   => $comment->id,
                        'type' => 'comment',
                    ],
                ],
            ]
        );
        $commentIds = Comment::where('book_id', $book->id)
            ->pluck('id')
            ->toArray();

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertEquals($commentIds, $book->comments->pluck('id')->toArray());
    }

    /**
     * @return void
     */
    public function testAssociateMorphMany(): void
    {
        $book = Book::first();
        $tag = Tag::where(
            [
                ['taggable_id', '!=', $book->id],
                ['taggable_type', '=', Book::class],
            ]
        )->first();
        $call = $this->post(
            '/forest/book/' . $book->id . '/relationships/tags',
            [
                'data' => [
                    [
                        'id'   => $tag->id,
                        'type' => 'tag',
                    ],
                ],
            ]
        );
        $tagIds = Tag::where(
            [
                'taggable_id'   => $book->id,
                'taggable_type' => Book::class,
            ]
        )->pluck('id')->toArray();

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertEquals($tagIds, $book->tags->pluck('id')->toArray());
    }

    /**
     * @return void
     */
    public function testAssociateBelongsToMany(): void
    {
        $book = Book::first();
        $range = Range::whereDoesntHave('books', static fn ($query) => $query->where('books.id', $book->id))->first();

        $call = $this->post(
            '/forest/book/' . $book->id . '/relationships/ranges',
            [
                'data' => [
                    [
                        'id'   => $range->id,
                        'type' => 'range',
                    ],
                ],
            ]
        );
        $rangeIds = array_values(Range::whereRelation('books', 'books.id', '=', $book->id)->pluck('id')->sort()->toArray());

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertEquals($rangeIds, array_values($book->ranges->pluck('id')->sort()->toArray()));
    }

    /**
     * @return void
     */
    public function testDissociateHasMany(): void
    {
        $book = Book::first();
        $movie = Movie::create(['body' => 'test movie', 'book_id' => $book->id]);
        $call = $this->delete(
            '/forest/book/' . $book->id . '/relationships/movies',
            [
                'data' => [
                    [
                        'id'   => $movie->id,
                        'type' => 'movie',
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNull(Movie::find($movie->id)->book_id);
    }

    /**
     * @return void
     */
    public function testDissociateMorphMany(): void
    {
        $book = Book::first();
        $sequel = Sequel::create(['label' => 'test movie', 'sequelable_type' => Book::class, 'sequelable_id' => $book->id]);
        $call = $this->delete(
            '/forest/book/1/relationships/sequels',
            [
                'data' => [
                    [
                        'id'   => $sequel->id,
                        'type' => 'sequel',
                    ],
                ],
            ]
        );

        $sequel = Sequel::find($sequel->id);
        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNull($sequel->sequelable_type);
        $this->assertNull($sequel->sequelable_id);
    }

    /**
     * @return void
     */
    public function testDissociateBelongsToMany(): void
    {
        $book = Book::first();
        $range = Range::whereRelation('books', 'books.id', '=', $book->id)->first();
        $call = $this->delete(
            '/forest/book/' . $book->id . '/relationships/ranges',
            [
                'data' => [
                    [
                        'id'   => $range->id,
                        'type' => 'range',
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNotContains([$range->id], $book->ranges->pluck('id')->toArray());
    }

    /**
     * @return void
     */
    public function testDissociateWithDelete(): void
    {
        $book = Book::first();
        $comment = Comment::where('book_id', $book->id)->first();
        $call = $this->delete(
            '/forest/book/' . $book->id . '/relationships/comments?delete=true',
            [
                'data' => [
                    [
                        'id'   => $comment->id,
                        'type' => 'comments',
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNull(Comment::find($comment->id));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDissociateExceptionRecordsNotFound(): void
    {
        $book = Book::first();
        $call = $this->delete(
            '/forest/book/' . $book->id . '/relationships/comments',
            [
                'data' => [
                    [
                        'id'   => '100',
                        'type' => 'comments',
                    ],
                ],
            ]
        );
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(409, $call->baseResponse->getStatusCode());
        $this->assertEquals($data['error'], '🌳🌳🌳 Record dissociate error: records not found');
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDissociateExceptionRecordsConstraint(): void
    {
        $book = Book::first();
        $comment = Comment::where('book_id', $book->id)->first();
        $call = $this->delete(
            '/forest/book/' . $book->id . '/relationships/comments',
            [
                'data' => [
                    [
                        'id'   => $comment->id,
                        'type' => 'comments',
                    ],
                ],
            ]
        );
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(409, $call->baseResponse->getStatusCode());
        $this->assertEquals($data['error'], '🌳🌳🌳 Record dissociate error: the records can not be dissociate');
    }

    /**
     * @return void
     */
    public function testUpdateBelongsTo(): void
    {
        $book = Book::first();
        $category = Category::create(['label' => 'foo']);
        $call = $this->put(
            '/forest/book/' . $book->id . '/relationships/category',
            [
                'data' => [
                    'id'   => $category->id,
                    'type' => 'category',
                ],
            ]
        );
        $book = $book->fresh();

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertEquals($category->id, $book->category_id);
    }

    /**
     * @return void
     */
    public function testUpdateHasOne(): void
    {
        $book = Book::first();
        $book->editor->book_id = null;
        $book->editor->save();
        $editor = Editor::create(['name' => 'foo', 'book_id' => null]);

        // TODO update test -> maybe set book_id nullable ?
        $call = $this->put(
            '/forest/book/' . $book->id . '/relationships/editor',
            [
                'data' => [
                    'id'   => $editor->id,
                    'type' => 'editor',
                ],
            ]
        );
        $book = $book->refresh();

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertEquals($editor->id, $book->editor->id);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testUpdateExceptionRecordsNotFound(): void
    {
        $book = Book::first();
        $category = Category::create(['label' => 'foo']);
        $call = $this->put(
            '/forest/book/' . $book->id . '/relationships/category',
            [
                'data' => [
                    'id'   => '100',
                    'type' => 'category',
                ],
            ]
        );

        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(409, $call->baseResponse->getStatusCode());
        $this->assertEquals($data['error'], '🌳🌳🌳 Record not found');
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testUpdateExceptionRecordsConstraint(): void
    {
        $book = Book::first();
        $advertisementOfBook2 = Advertisement::firstWhere('book_id', 2);
        $call = $this->put(
            '/forest/book/' . $book->id . '/relationships/advertisement',
            [
                'data' => [
                    'id'   => $advertisementOfBook2->id,
                    'type' => 'advertisement',
                ],
            ]
        );

        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(409, $call->baseResponse->getStatusCode());
        $this->assertEquals($data['error'], '🌳🌳🌳 The record can not be updated');
    }
}
