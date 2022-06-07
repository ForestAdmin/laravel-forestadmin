<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasSearch;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Mock\CustomModel;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class HasSearchTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasSearchTest extends TestCase
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testGetFieldsToSearch(): void
    {
        $fields = [
            [
                'field'      => 'id',
                'type'       => 'Number',
                'enums'      => null,
                'is_virtual' => false,
                'reference'  => null,
            ],
            [
                'field'      => 'foo',
                'type'       => 'String',
                'enums'      => null,
                'is_virtual' => false,
                'reference'  => null,
            ],
            [
                'field'      => 'bar',
                'type'       => 'Enum',
                'enums'      => ['a', 'b'],
                'is_virtual' => false,
                'reference'  => null,
            ],
            [
                'field'      => 'label',
                'type'       => 'String',
                'enums'      => null,
                'is_virtual' => true,
                'reference'  => null,
            ],
            [
                'field'      => 'description',
                'type'       => 'String',
                'enums'      => null,
                'is_virtual' => true,
                'reference'  => 'bar.id',
            ],
        ];
        $trait = $this->getObjectForTrait(HasSearch::class);
        $model = m::mock(Model::class)->makePartial();
        App::shouldReceive('basePath')->andReturn(null);
        File::shouldReceive('get')->andReturn(null);
        ForestSchema::shouldReceive('getFields')->andReturn($fields);

        $getFields = $this->invokeMethod($trait, 'getFieldsToSearch', [$model]);

        $this->assertCount(4, $getFields);
        $this->assertEquals([$fields[0], $fields[1], $fields[2], $fields[3]], $getFields);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFieldInSearchFieldsModelDoesNotHaveSearchFieldsMethod(): void
    {
        $trait = $this->getObjectForTrait(HasSearch::class);
        $model = m::mock(Model::class)->makePartial();

        $searchFields = $this->invokeMethod($trait, 'fieldInSearchFields', [$model, 'foo']);

        $this->assertTrue($searchFields);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFieldInSearchFieldsModelHasSearchFieldsMethod(): void
    {
        $trait = $this->getObjectForTrait(HasSearch::class);
        $model = m::mock(CustomModel::class)->makePartial();

        $searchFields = $this->invokeMethod($trait, 'fieldInSearchFields', [$model, 'bar']);

        $this->assertFalse($searchFields);
    }

    /**
     * @param Model  $model
     * @param string $field
     * @return bool
     */
    protected function fieldInSearchFields(Model $model, string $field): bool
    {
        return method_exists($model, 'searchFields') &&
            (empty($model->searchFields()) || in_array($field, $model->searchFields(), true));
    }
}
