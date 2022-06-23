<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Services\ForestSchemaInstrospection;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
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
    public function testGetClass(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getClass('book');
        $schemaCollection = $this->fakeSchema(false)['collections'];
        $expected = $schemaCollection[array_search('book', array_column($schemaCollection, 'name'))]['class'];

        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetFields(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getFields('book');
        $schemaCollection = $this->fakeSchema(false)['collections'];
        $expected = $schemaCollection[array_search('book', array_column($schemaCollection, 'name'))]['fields'];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetFieldsCollectionDoesNotExists(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getFields('foo');

        $this->assertIsArray($data);
        $this->assertEmpty($data);
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
     * @return void
     * @throws \JsonException
     */
    public function testGetTypeByField(): void
    {
        $forestSchema = $this->makeForestSchema();
        $model = new Book();

        $result = $forestSchema->getTypeByField(class_basename($model), 'label');
        $resultUnknownType = $forestSchema->getTypeByField(class_basename($model), 'foo');

        $this->assertEquals('String', $result);
        $this->assertNull($resultUnknownType);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartFields(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartFields('book');
        $schemaCollection = $this->fakeSchema(false)['collections'];
        $expected = $schemaCollection[array_search('book', array_column($schemaCollection, 'name'))]['fields'];
        foreach ($expected as $key => $value) {
            if ($value['is_virtual'] && $value['reference'] === null) {
                $expected[$value['field']] = $value;
            }
            unset($expected[$key]);
        }

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartFieldsCollectionDoesNotExists(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartFields('foo');

        $this->assertIsArray($data);
        $this->isEmpty($data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartActions(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartActions('book');
        $schemaCollection = $this->fakeSchema(false)['collections'];
        $expected = $schemaCollection[array_search('book', array_column($schemaCollection, 'name'))]['actions'];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartActionsCollectionDoesNotExists(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartActions('foo');

        $this->assertIsArray($data);
        $this->isEmpty($data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartSegments(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartSegments('category');
        $schemaCollection = $this->fakeSchema(false)['collections'];
        $expected = $schemaCollection[array_search('category', array_column($schemaCollection, 'name'))]['segments'];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartSegmentsCollectionDoesNotExists(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartSegments('foo');

        $this->assertIsArray($data);
        $this->isEmpty($data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartRelationships(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartRelationships('book');
        $schemaCollection = $this->fakeSchema(false)['collections'];
        $expected = $schemaCollection[array_search('book', array_column($schemaCollection, 'name'))]['fields'];
        foreach ($expected as $key => $value) {
            if ($value['is_virtual'] && $value['reference'] !== null) {
                $expected[$value['field']] = $value;
            }
            unset($expected[$key]);
        }

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testGetSmartRelationshipsCollectionDoesNotExists(): void
    {
        $forestSchema = $this->makeForestSchema();
        $data = $forestSchema->getSmartRelationships('foo');

        $this->assertIsArray($data);
        $this->isEmpty($data);
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
