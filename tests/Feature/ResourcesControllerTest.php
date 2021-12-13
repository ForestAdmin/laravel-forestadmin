<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;

/**
 * Class ResourcesControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourcesControllerTest extends TestCase
{
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
    public function testIndex(): void
    {
        $book = $this->getBook();
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->get('/forest/Book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('Book', $data['data'][0]['type']);
        $this->assertEquals($book->id, $data['data'][0]['id']);
        $this->assertEquals($book->label, $data['data'][0]['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testShow(): void
    {
        $book = $this->getBook();
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->get('/forest/Book/1?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('Book', $data['data']['type']);
        $this->assertEquals($book->id, $data['data']['id']);
        $this->assertEquals($book->label, $data['data']['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testCount(): void
    {
        $book = $this->getBook();
        $call = $this->get('/forest/Book/count');
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals($book->count(), $data['count']);
    }

    /**
     * @return Book
     */
    public function getBook()
    {
        $book = new Book();
        $book->id = 1;
        $book->label = 'foo';
        $book->comment = 'test value';
        $book->difficulty = 'easy';
        $book->amount = 50.20;
        $book->options = [];
        $book->category_id = 1;
        $book->save();

        return $book;
    }
}
