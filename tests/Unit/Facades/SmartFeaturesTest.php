<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Facades;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Facades\SmartFeatures;
use ForestAdmin\LaravelForestAdmin\Services\ForestSchemaInstrospection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartFeaturesHandler;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Mockery as m;

/**
 * Class SmartFeaturesTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartFeaturesTest extends TestCase
{
    use FakeSchema;

    /**
     * @return void
     * @throws \JsonException
     */
    public function testHandleSmartFields(): void
    {
        $book = new Book();
        $response = $book;
        $smartFeature = m::mock(SmartFeaturesHandler::class)
            ->shouldReceive('handleSmartFields')
            ->withArgs([$book])
            ->once()
            ->andReturn($response)
            ->getMock();

        app()->instance('smart-features', $smartFeature);
        $facadeCall = SmartFeatures::handleSmartFields($book);

        $this->assertEquals($response, $facadeCall);
    }
}
