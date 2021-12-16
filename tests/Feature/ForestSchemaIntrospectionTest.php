<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Services\ForestSchemaInstrospection;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use JsonPath\JsonObject;

/**
 * Class ForestSchemaIntrospectionTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestSchemaIntrospectionTest extends TestCase
{
    use FakeSchema;

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSchema(): void
    {
        $forestSchema = $this->makeForestSchema();
        $schema = $forestSchema->getSchema();

        $this->assertInstanceOf(JsonObject::class, $schema);
        $this->assertEquals($this->fakeSchema(false)['collections'], $schema->get('$.collections')[0]);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetRelatedData(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getRelatedData('book');

        $this->assertIsArray($data);
        $this->assertEquals('comments', $data[0]);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetRelatedDataCollectionDoesNotExists(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getRelatedData('foo');

        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    /**
     * @return ForestSchemaInstrospection
     * @throws \JsonException
     */
    public function makeForestSchema(): ForestSchemaInstrospection
    {
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        return new ForestSchemaInstrospection();
    }

}
