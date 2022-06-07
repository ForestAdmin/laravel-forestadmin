<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use ForestAdmin\LaravelForestAdmin\Schema\ForestModel;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartSegment;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Mock\CustomModel;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Mockery as m;

/**
 * Class ForestModelTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestModelTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSerialize(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();

        $forestModel->shouldReceive('getFields')
            ->andReturn(
                [
                    [
                        'field'         => 'label',
                        'is_required'   => true,
                        'type'          => 'String',
                        'default_value' => null,
                        'enums'         => null,
                        'integration'   => null,
                        'is_filterable' => true,
                        'is_read_only'  => false,
                        'is_sortable'   => true,
                        'is_virtual'    => false,
                        'reference'     => null,
                        'inverse_of'    => null,
                        'widget'        => null,
                        'validations'   => [],

                    ],
                ]
            );

        $serialize = $forestModel->serialize();

        $this->assertArrayHasKey('name', $serialize);
        $this->assertArrayHasKey('old_name', $serialize);
        $this->assertArrayHasKey('icon', $serialize);
        $this->assertArrayHasKey('is_read_only', $serialize);
        $this->assertArrayHasKey('is_virtual', $serialize);
        $this->assertArrayHasKey('only_for_relationships', $serialize);
        $this->assertArrayHasKey('pagination_type', $serialize);
        $this->assertArrayHasKey('fields', $serialize);
        $this->assertEquals('label', $serialize['fields'][0]['field']);
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testGetFields(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $forestModel->shouldReceive('fetchFieldsFromTable')
            ->andReturn(
                collect(
                    [
                        'foo' => [
                            'field'         => 'foo',
                            'enums'         => null,
                            'type'          => 'Enum',
                            'default_value' => null,
                            'integration'   => null,
                            'is_filterable' => true,
                            'is_read_only'  => false,
                            'is_required'   => true,
                            'is_sortable'   => true,
                            'is_virtual'    => false,
                            'reference'     => null,
                            'inverse_of'    => null,
                            'widget'        => null,
                            'validations'   => [],
                        ],
                        'bar' => [
                            'field'         => 'bar',
                            'type'          => 'Number',
                            'default_value' => null,
                            'enums'         => null,
                            'integration'   => null,
                            'is_filterable' => true,
                            'is_read_only'  => false,
                            'is_required'   => true,
                            'is_sortable'   => true,
                            'is_virtual'    => false,
                            'reference'     => null,
                            'inverse_of'    => null,
                            'widget'        => null,
                            'validations'   => [],
                        ],
                    ]
                )
            );

        $fields = $forestModel->getFields();
        $foo = array_search('foo', array_column($fields, 'field'), true);
        $bar = array_search('bar', array_column($fields, 'field'), true);
        $label = array_search('label', array_column($fields, 'field'), true);
        $defaultValues = $this->invokeMethod($forestModel, 'fieldDefaultValues');

        $this->assertIsArray($fields);
        $this->assertNotNull($foo);
        $this->assertNotNull($bar);
        $this->assertNotNull($label);
        $this->assertEquals($fields[$bar]['type'], 'Enum');
        $this->assertEquals($fields[$bar]['enums'], ['easy', 'hard']);
        $this->assertEquals($fields[$label]['default_value'], $defaultValues['default_value']);
        $this->assertEquals($fields[$label]['enums'], $defaultValues['enums']);
        $this->assertEquals($fields[$label]['integration'], $defaultValues['integration']);
        $this->assertEquals($fields[$label]['is_filterable'], $defaultValues['is_filterable']);
        $this->assertEquals($fields[$label]['is_read_only'], $defaultValues['is_read_only']);
        $this->assertEquals($fields[$label]['is_required'], $defaultValues['is_required']);
        $this->assertEquals($fields[$label]['is_sortable'], $defaultValues['is_sortable']);
        $this->assertEquals($fields[$label]['is_virtual'], $defaultValues['is_virtual']);
        $this->assertEquals($fields[$label]['reference'], $defaultValues['reference']);
        $this->assertEquals($fields[$label]['widget'], $defaultValues['widget']);
        $this->assertEquals($fields[$label]['validations'], $defaultValues['validations']);
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testFetchFieldsFromTable(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $forestModel->shouldReceive('getRelations')
            ->withAnyArgs()
            ->andReturn([]);

        $fields = $forestModel->fetchFieldsFromTable();

        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertNull($fields->firstWhere('field', 'field_exclude'));
        $this->assertIsArray($fields['id']);
        $this->assertEquals($fields['id']['field'], 'id');
        $this->assertEquals($fields['id']['type'], 'Number');
        $this->assertEquals($fields['id']['is_required'], false);
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testGetRelations(): void
    {
        $dummyModel = new Book();
        $forestModel = m::mock(ForestModel::class, [$dummyModel])
            ->makePartial();
        $publicMethods = [];

        $relations = $forestModel->getRelations($forestModel->getModel());
        foreach ((new \ReflectionClass($forestModel->getModel()))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $publicMethods[$method->getName()] = (string) $method->getReturnType();
        }

        $this->assertIsArray($relations);
        foreach ($relations as $key => $value) {
            $this->assertcontains($key, array_keys($publicMethods));
            $this->assertcontains($value, $publicMethods);
        }
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testGetSingleRelations(): void
    {
        $dummyModel = new Book();
        $forestModel = m::mock(ForestModel::class, [$dummyModel])
            ->makePartial();
        $publicMethods = [];

        $relations = $forestModel->getSingleRelations($forestModel->getModel());
        foreach ((new \ReflectionClass($forestModel->getModel()))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array((string) $method->getReturnType(), [BelongsTo::class, HasOne::class], true)) {
                $publicMethods[$method->getName()] = (string) $method->getReturnType();
            }
        }

        $this->assertIsArray($relations);
        foreach ($relations as $key => $value) {
            $this->assertcontains($key, array_keys($publicMethods));
            $this->assertcontains($value, $publicMethods);
        }
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsBelongsTo(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldCategory = $merge->firstWhere('field', 'category');
        $category = $forestModel->getModel()->category();
        $this->assertNotNull($fieldCategory);
        $this->assertEquals($fieldCategory['relationship'], $forestModel->mapRelationships(BelongsTo::class));
        $this->assertEquals($fieldCategory['reference'], Str::camel(class_basename($category->getRelated())) . '.' . $category->getRelated()->getKeyName());
        $this->assertEquals($fieldCategory['inverse_of'], Str::camel($forestModel->getName()));
        $this->assertEquals($fieldCategory['type'], 'Number');
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsBelongsToMany(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldRange = $merge->firstWhere('field', 'ranges');
        $ranges = $forestModel->getModel()->ranges();
        $this->assertNotNull($fieldRange);
        $this->assertEquals($fieldRange['relationship'], $forestModel->mapRelationships(BelongsToMany::class));
        $this->assertEquals($fieldRange['field'], 'ranges');
        $this->assertEquals($fieldRange['reference'], Str::camel(class_basename($ranges->getRelated())) . '.' . $ranges->getRelated()->getKeyName());
        $this->assertEquals($fieldRange['inverse_of'], Str::camel($forestModel->getName()));
        $this->assertEquals($fieldRange['type'], ['Number']);
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsHasMany(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldComment = $merge->firstWhere('field', 'comments');
        $comments = $forestModel->getModel()->comments();
        $this->assertNotNull($fieldComment);
        $this->assertEquals($fieldComment['relationship'], $forestModel->mapRelationships(HasMany::class));
        $this->assertEquals($fieldComment['field'], 'comments');
        $this->assertEquals($fieldComment['reference'], Str::camel(class_basename($comments->getRelated())) . '.' . $comments->getRelated()->getKeyName());
        $this->assertEquals($fieldComment['inverse_of'], Str::camel($forestModel->getName()));
        $this->assertEquals($fieldComment['type'], ['Number']);
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsHasOne(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $editor = $merge->firstWhere('field', 'editor');
        $editors = $forestModel->getModel()->editor();
        $this->assertNotNull($editor);
        $this->assertEquals($editor['relationship'], $forestModel->mapRelationships(HasOne::class));
        $this->assertEquals($editor['field'], 'editor');
        $this->assertEquals($editor['reference'], Str::camel(class_basename($editors->getRelated())) . '.' . $editors->getRelated()->getKeyName());
        $this->assertEquals($editor['inverse_of'], Str::camel($forestModel->getName()));
        $this->assertEquals($editor['type'], 'Number');
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetName(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'model_name';
        $forestModel->setName($value);

        $this->assertEquals(Str::camel($value), $forestModel->getName());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetOldName(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'old_name';
        $forestModel->setOldName($value);

        $this->assertEquals(Str::camel($value), $forestModel->getOldName());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetIcon(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'icon';
        $forestModel->setIcon($value);

        $this->assertEquals($value, $forestModel->getIcon());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetIsReadOnly(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = true;
        $forestModel->setIsReadOnly($value);

        $this->assertEquals($value, $forestModel->isReadOnly());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetIsSearchable(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = true;
        $forestModel->setIsSearchable($value);

        $this->assertEquals($value, $forestModel->isSearchable());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetIsVirtual(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = true;
        $forestModel->setIsVirtual($value);

        $this->assertEquals($value, $forestModel->isVirtual());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetOnlyForRelationships(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = true;
        $forestModel->setOnlyForRelationships($value);

        $this->assertEquals($value, $forestModel->isOnlyForRelationships());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetPaginationType(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'Page';
        $forestModel->setPaginationType($value);

        $this->assertEquals($value, $forestModel->getPaginationType());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetTable(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'table';
        $forestModel->setTable($value);

        $this->assertEquals($value, $forestModel->getTable());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetDatabase(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'db';
        $forestModel->setDatabase($value);

        $this->assertEquals($value, $forestModel->getDatabase());
    }

    /**
     * @return void
     */
    public function testFetchSmartFeaturesWithSmartAction(): void
    {
        $book = Book::first();
        $forestModel = m::mock(ForestModel::class, [$book])
            ->makePartial();
        $value = $forestModel->fetchSmartFeatures(SmartAction::class);

        $this->assertInstanceOf(Collection::class, $value);
        $this->assertEquals(2, $value->count());
        $this->assertEquals('Book.smart action bulk', $value->first()['id']);
        $this->assertEquals('Book.smart action single', $value->last()['id']);
    }

    /**
     * @return void
     */
    public function testFetchSmartFeaturesWithSmartRelationship(): void
    {
        $book = Book::first();
        $forestModel = m::mock(ForestModel::class, [$book])
            ->makePartial();
        $value = $forestModel->fetchSmartFeatures(SmartRelationship::class);
        $smartRelationship = new SmartRelationship(
            [
                'type'      => ['String'],
                'reference' => 'bookstore.id',
                'field'     => 'smartBookstores',
            ]
        );

        $this->assertInstanceOf(Collection::class, $value);
        $this->assertEquals(1, $value->count());
        $this->assertEquals($smartRelationship->serialize(), $value->first());
    }

    /**
     * @return void
     */
    public function testFetchSmartFeaturesWithSmartField(): void
    {
        $book = Book::first();
        $forestModel = m::mock(ForestModel::class, [$book])
            ->makePartial();
        $value = $forestModel->fetchSmartFeatures(SmartField::class);
        $smartField = new SmartField(['type' => 'String', 'field' => 'reference', 'is_filterable' => true]);

        $this->assertInstanceOf(Collection::class, $value);
        $this->assertEquals(1, $value->count());
        $this->assertEquals($smartField->serialize(), $value->first());
    }

    /**
     * @return void
     */
    public function testFetchSmartFeaturesWithSmartSegment(): void
    {
        $category = Category::first();
        $forestModel = m::mock(ForestModel::class, [$category])
            ->makePartial();
        $value = $forestModel->fetchSmartFeatures(SmartSegment::class);
        $smartSegment = new SmartSegment(
            class_basename($category),
            'bestName',
            'bestCategories',
            fn(Builder $query) => $query->where('id', '<', 3),
        );

        $this->assertInstanceOf(Collection::class, $value);
        $this->assertEquals(1, $value->count());
        $this->assertEquals($smartSegment->serialize(), $value->first());
    }

    /**
     * @return object
     * @throws Exception
     * @throws SchemaException
     */
    public function getLaravelModel()
    {
        $schemaManager = $this->prophesize(AbstractSchemaManager::class);
        $schemaManager->getDatabasePlatform()->willReturn(null);
        $schemaManager->listTableColumns(Argument::any(), Argument::any())
            ->willReturn(
                [
                    'id'            => new Column('id', Type::getType('bigint')),
                    'foo'           => new Column('foo', Type::getType('string')),
                    'field_exclude' => new Column('field_exclude', Type::getType('blob')),
                ]
            );

        $connection = $this->prophesize(Connection::class);
        $connection->getTablePrefix()
            ->shouldBeCalled()
            ->willReturn('prefix.');
        $connection->getDoctrineSchemaManager()
            ->willReturn($schemaManager->reveal());

        $model = $this->prophesize(CustomModel::class);
        $model
            ->getConnection()
            ->shouldBeCalled()
            ->willReturn($connection->reveal());
        $model
            ->getTable()
            ->shouldBeCalledOnce()
            ->willReturn('dummy_tables');
        $model
            ->getKeyName()
            ->willReturn('id');
        $model
            ->schemaFields()
            ->willReturn(
                [
                    ['field' => 'label', 'is_required' => false],
                    ['field' => 'bar', 'enums' => ['easy', 'hard']],
                ]
            );

        return $model->reveal();
    }

    /**
     * @return array
     */
    public function makeForestModel(): array
    {
        $dummyModel = new Book();
        $forestModel = m::mock(ForestModel::class, [$dummyModel])->makePartial();

        $fields = collect(
            [
                'category_id' => ['field' => 'category_id'],
            ]
        );

        return [$forestModel, $fields];
    }
}
