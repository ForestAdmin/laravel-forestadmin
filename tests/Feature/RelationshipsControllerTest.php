<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Advertisement;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Editor;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Movie;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Sequel;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Tag;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Foundation\Application;
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
    use FakeData;
    use FakeSchema;
    use MockForestUserFactory;
    use ScopeManagerFactory;

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('forest.models_namespace', 'ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\\');
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function setUp(): void
    {
        parent::setUp();

        $forestUser = new ForestUser(
            [
                'id'           => 1,
                'email'        => 'john.doe@forestadmin.com',
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'rendering_id' => 1,
                'tags'         => [],
                'teams'        => 'Operations',
                'exp'          => 1643825269,
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
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIndex(): void
    {
        $this->getBook()->save();
        $this->getComments();

        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

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
    public function testCount(): void
    {
        $this->getBook()->save();
        $this->getComments();
        $call = $this->get('/forest/book/1/relationships/comments/count');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(Comment::count(), $data['count']);
    }

    /**
     * @return void
     */
    public function testAssociateHasMany(): void
    {
        $this->getBook()->save();
        $this->getComments();
        $book = Book::first();
        $call = $this->post(
            '/forest/book/1/relationships/comments',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'comment',
                    ]
                ]
            ]
        );
        $commentIds = Comment::pluck('id')->toArray();

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
        $this->getBook()->save();
        $this->getTags();
        $book = Book::first();
        $call = $this->post(
            '/forest/book/1/relationships/tags',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'tag',
                    ]
                ]
            ]
        );
        $tagIds = Tag::pluck('id')->toArray();

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
        $this->getBook()->save();
        $this->getRanges();
        $call = $this->post(
            '/forest/book/1/relationships/ranges',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'range',
                    ]
                ]
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
    }

    /**
     * @return void
     */
    public function testDissociateHasMany(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        Movie::create(['body' => 'test movie', 'book_id' => $book->id]);
        $call = $this->delete(
            '/forest/book/1/relationships/movies',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'movie',
                    ]
                ]
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNull(Movie::find(1)->book_id);
    }

    /**
     * @return void
     */
    public function testDissociateMorphMany(): void
    {
        $this->getBook()->save();
        $book = Book::first();
        Sequel::create(['label' => 'test movie', 'sequelable_type' => Book::class, 'sequelable_id' => $book->id]);
        $call = $this->delete(
            '/forest/book/1/relationships/sequels',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'sequel',
                    ]
                ]
            ]
        );

        $sequel = Sequel::find(1);
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
        $this->getBook()->save();
        $book = Book::first();
        $this->getRanges();
        $call = $this->delete(
            '/forest/book/1/relationships/ranges',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'range',
                    ]
                ]
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNotContains([1], $book->ranges->pluck('id')->toArray());
    }

    /**
     * @return void
     */
    public function testDissociateWithDelete(): void
    {
        $this->getBook()->save();
        $this->getComments();
        $call = $this->delete(
            '/forest/book/1/relationships/comments?delete=true',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'comments',
                    ]
                ]
            ]
        );

        $this->assertInstanceOf(Response::class, $call->baseResponse);
        $this->assertEquals(204, $call->baseResponse->getStatusCode());
        $this->assertEmpty($call->baseResponse->getContent());
        $this->assertNull(Comment::find(1));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testDissociateExceptionRecordsNotFound(): void
    {
        $this->getBook()->save();
        $this->getComments();
        $call = $this->delete(
            '/forest/book/1/relationships/comments',
            [
                'data' => [
                    [
                        'id'   => '100',
                        'type' => 'comments',
                    ]
                ]
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
        $this->getBook()->save();
        $this->getComments();
        $call = $this->delete(
            '/forest/book/1/relationships/comments',
            [
                'data' => [
                    [
                        'id'   => '1',
                        'type' => 'comments',
                    ]
                ]
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
        $this->getBook()->save();
        $book = Book::first();
        $category = Category::create(['label' => 'foo']);
        $call = $this->put(
            '/forest/book/1/relationships/category',
            [
                'data' => [
                    'id'   => $category->id,
                    'type' => 'category',
                ]
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
        $this->getBook()->save();
        $book = Book::first();
        $editor = Editor::create(['name' => 'foo', 'book_id' => 2]);
        $call = $this->put(
            '/forest/book/1/relationships/editor',
            [
                'data' => [
                    'id'   => $editor->id,
                    'type' => 'editor',
                ]
            ]
        );
        $book = $book->fresh();

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
        $this->getBook()->save();
        $category = Category::create(['label' => 'foo']);
        $call = $this->put(
            '/forest/book/1/relationships/category',
            [
                'data' => [
                    'id'   => '100',
                    'type' => 'category',
                ]
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
        for ($i = 0; $i < 2; $i++) {
            $this->getBook()->save();
            Advertisement::create(['label' => 'foo', 'book_id' => $i + 1]);
        }
        $advertisementOfBook2 = Advertisement::firstWhere('book_id', 2);
        $call = $this->put(
            '/forest/book/1/relationships/advertisement',
            [
                'data' => [
                    'id'   => $advertisementOfBook2->id,
                    'type' => 'advertisement',
                ]
            ]
        );

        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals(409, $call->baseResponse->getStatusCode());
        $this->assertEquals($data['error'], '🌳🌳🌳 The record can not be updated');
    }
}
