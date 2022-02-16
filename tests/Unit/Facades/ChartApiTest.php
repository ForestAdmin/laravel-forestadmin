<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Facades;

use ForestAdmin\LaravelForestAdmin\Facades\ChartApi;
use ForestAdmin\LaravelForestAdmin\Services\ChartApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Mockery as m;

/**
 * Class ChartApiTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartApiTest extends TestCase
{
    /**
     * @return void
     */
    public function testRender(): void
    {
        $response = response()->json(
            [
                'data' => [
                    'type'       => 'stats',
                    'id'         => 'f8beb84f-86d3-40f8-8978-5cfc26b5a882',
                    'attributes' => [
                        'value' => [
                            'countCurrent' => 1,
                        ],
                    ],
                ],
            ]
        );

        $chartApi = m::mock(ChartApiResponse::class)
            ->shouldReceive('renderValue')
            ->withArgs([1])
            ->once()
            ->andReturn($response)
            ->getMock();

        app()->instance('chart-api', $chartApi);
        $facadeCall = ChartApi::renderValue(1);

        $this->assertInstanceOf(JsonResponse::class, $facadeCall);
        $this->assertEquals($response, $facadeCall);
    }
}
