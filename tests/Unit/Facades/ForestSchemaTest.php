<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Facades;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Services\ForestSchemaInstrospection;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Mockery as m;

/**
 * Class ForestSchemaTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestSchemaTest extends TestCase
{
    use FakeSchema;

    /**
     * @return void
     * @throws \JsonException
     */
    public function testRender(): void
    {
        $response = ['comments'];
        $forestSchema = m::mock(ForestSchemaInstrospection::class)
            ->shouldReceive('getRelatedData')
            ->withArgs(['book'])
            ->once()
            ->andReturn($response)
            ->getMock();

        app()->instance('forest-schema', $forestSchema);
        $facadeCall = ForestSchema::getRelatedData('book');

        $this->assertEquals($response, $facadeCall);
    }
}
