<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

/**
 * Class ResourcesControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourcesControllerTest extends TestCase
{
    use FakeData;
    use FakeSchema;

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
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label']];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/Book?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::first();

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
        $this->getBook()->save();
        $params = ['fields' => ['book' => 'id,label']];
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $call = $this->get('/forest/Book/1?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $book = Book::first();

        $this->assertInstanceOf(JsonResponse::class, $call->baseResponse);
        $this->assertEquals('Book', $data['data']['type']);
        $this->assertEquals($book->id, $data['data']['id']);
        $this->assertEquals($book->label, $data['data']['attributes']['label']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testShowException(): void
    {
        $params = ['fields' => ['book' => 'id,label']];
        $call = $this->get('/forest/Book/9999?' . http_build_query($params));
        $data = json_decode($call->baseResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals("🌳🌳🌳 Collection not found", $data['error']);
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
}
