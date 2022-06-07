<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\ChartApiResponse;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class ChartApiResponseTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartApiResponseTest extends TestCase
{
    /**
     * @return void
     * @throws \JsonException
     */
    public function testRenderValue(): void
    {
        $chartApi = new ChartApiResponse();
        $toJson = $chartApi->renderValue(100);
        $content = json_decode($toJson->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('type', $content['data']);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertArrayHasKey('attributes', $content['data']);
        $this->assertArrayHasKey('value', $content['data']['attributes']);
        $this->assertEquals('stats', $content['data']['type']);
        $this->assertEquals(['countCurrent' => 100], $content['data']['attributes']['value']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testRenderPie(): void
    {
        $chartApi = new ChartApiResponse();
        $data = [['key' => 'foo', 'value' => 10], ['key' => 'bar', 'value' => 20]];
        $toJson = $chartApi->renderPie($data);
        $content = json_decode($toJson->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('type', $content['data']);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertArrayHasKey('attributes', $content['data']);
        $this->assertArrayHasKey('value', $content['data']['attributes']);
        $this->assertEquals('stats', $content['data']['type']);
        $this->assertEquals($data, $content['data']['attributes']['value']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testRenderLine(): void
    {
        $chartApi = new ChartApiResponse();
        $data = [
            [
                'label'  => 'foo',
                'values' => [
                    ['value' => 10],
                    ['value' => 20],
                ],
            ],
            [
                'label'  => 'bar',
                'values' => [
                    ['value' => 30],
                    ['value' => 40],
                ],
            ],
        ];
        $toJson = $chartApi->renderLine($data);
        $content = json_decode($toJson->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('type', $content['data']);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertArrayHasKey('attributes', $content['data']);
        $this->assertArrayHasKey('value', $content['data']['attributes']);
        $this->assertEquals('stats', $content['data']['type']);
        $this->assertEquals($data, $content['data']['attributes']['value']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testRenderObjective(): void
    {
        $chartApi = new ChartApiResponse();
        $data = [
            'objective' => 100,
            'value'     => 50,
        ];
        $toJson = $chartApi->renderObjective($data);
        $content = json_decode($toJson->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('type', $content['data']);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertArrayHasKey('attributes', $content['data']);
        $this->assertArrayHasKey('value', $content['data']['attributes']);
        $this->assertEquals('stats', $content['data']['type']);
        $this->assertEquals($data, $content['data']['attributes']['value']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testRenderLeaderboard(): void
    {
        $chartApi = new ChartApiResponse();
        $data = [['key' => 'foo', 'value' => 10], ['key' => 'bar', 'value' => 20]];
        $toJson = $chartApi->renderLeaderboard($data);
        $content = json_decode($toJson->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('type', $content['data']);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertArrayHasKey('attributes', $content['data']);
        $this->assertArrayHasKey('value', $content['data']['attributes']);
        $this->assertEquals('stats', $content['data']['type']);
        $this->assertEquals($data, $content['data']['attributes']['value']);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testToJson(): void
    {
        $chartApi = new ChartApiResponse();
        $toJson = $chartApi->toJson(100);
        $content = json_decode($toJson->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('type', $content['data']);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertArrayHasKey('attributes', $content['data']);
        $this->assertArrayHasKey('value', $content['data']['attributes']);
        $this->assertEquals('stats', $content['data']['type']);
        $this->assertEquals(100, $content['data']['attributes']['value']);
    }
}
