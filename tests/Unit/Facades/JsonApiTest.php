<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Facades;

use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Services\JsonApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
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
     */
    public function testRender(): void
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

    /**
     * @return void
     */
    public function testRenderItem(): void
    {
        $response = [
                'data' => [
                    'type'       => 'data',
                    'id'         => '1',
                    'attributes' => [
                        'foo' => 'bar',
                    ],
                ]
            ];
        $data = ['id'  => 1,'foo' => 'bar'];

        $jsonApi = m::mock(JsonApiResponse::class)
            ->shouldReceive('renderItem')
            ->withArgs([$data, 'data', TestTransformers::class])
            ->once()
            ->andReturn($response)
            ->getMock();

        app()->instance('json-api', $jsonApi);
        $facadeCall = JsonApi::renderItem($data, 'data', TestTransformers::class);

        $this->assertEquals($response, $facadeCall);
    }

    public function testDeactivateCountResponse(): void
    {
        $response = response()->json([
            'meta' => [
                'count' => 'deactivated'
            ],
        ]);

        $jsonApi = m::mock(JsonApiResponse::class)
            ->shouldReceive('deactivateCountResponse')
            ->once()
            ->andReturn($response)
            ->getMock();

        app()->instance('json-api', $jsonApi);
        $facadeCall = JsonApi::deactivateCountResponse();

        $this->assertEquals($response, $facadeCall);
    }
}
