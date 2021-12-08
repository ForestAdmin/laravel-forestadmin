<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Facades;

use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Mockery as m;

/**
 * Class JsonApiTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class JsonApiTest extends TestCase
{
    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSerialize(): void
    {
        $book = new Book();
        $book->label = 'foo';
        $book->category_id = 10;

        $response = [
            'data'     => [
                [
                    'type'       => 'Book',
                    'id'         => 1,
                    'attributes' => [
                        'label'  => $book->label,
                        'category_id' => $book->category_id,
                    ],
                ],
            ],
            'included' => [
                'type'       => 'Category',
                'id'         => 10,
                'attributes' => [
                    'name' => 'bar',
                ],
            ],
        ];

        $jsonApi = m::mock(JsonApiResponse::class)
            ->shouldReceive('render')
            ->withArgs([$book, 'book'])
            ->once()
            ->andReturn($response)
            ->getMock();

        app()->instance('json-api', $jsonApi);
        $facadeCall = JsonApi::render($book, 'book');

        $this->assertEquals($response, $facadeCall);
    }
}
