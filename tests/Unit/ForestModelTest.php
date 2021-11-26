<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use ForestAdmin\LaravelForestAdmin\Schema\ForestModel;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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

                    ]
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
                        ]
                    ]
                )
            );
        $forestModel->setFields(
            [
                ['field' => 'label', 'is_required' => false],
                ['field' => 'bar', 'enums' => ['easy', 'hard']],
            ]
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
     * @throws Exception
     * @throws SchemaException
     * @return void
     */
    public function testFetchFieldsFromTable(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $forestModel->shouldReceive('getRelations')
            ->withAnyArgs()
            ->andReturn([]);
        $defaultValues = $this->invokeMethod($forestModel, 'fieldDefaultValues');

        $fields = $forestModel->fetchFieldsFromTable();

        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertIsArray($fields['id']);
        $this->assertEquals($fields['id']['field'], 'id');
        $this->assertEquals($fields['id']['type'], 'Number');
        $this->assertEquals($fields['id']['is_required'], true);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     * @return void
     */
    public function testGetRelations(): void
    {
        $dummyModel = new Book();
        $forestModel = m::mock(ForestModel::class, [$dummyModel])
            ->makePartial();

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
        $this->assertEquals($fieldCategory['reference'], $category->getRelated()->getTable() . '.' . $category->getOwnerKeyName());
        $this->assertEquals($fieldCategory['inverse_of'], $category->getOwnerKeyName());
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
        $this->assertEquals($fieldRange['inverse_of'], $ranges->getRelatedPivotKeyName());
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
        $this->assertEquals($fieldComment['reference'], $comments->getRelated()->getTable() . '.' . $comments->getForeignKeyName());
        $this->assertEquals($fieldComment['inverse_of'], $comments->getForeignKeyName());
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsHasManyThrough(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldDeployment = $merge->firstWhere('field', 'deployments');
        $deployments = $forestModel->getModel()->deployments();
        $this->assertNotNull($fieldDeployment);
        $this->assertEquals($fieldDeployment['relationship'], $forestModel->mapRelationships(HasManyThrough::class));
        $this->assertEquals($fieldDeployment['field'], 'deployments');
        $this->assertEquals($fieldDeployment['reference'], $deployments->getRelated()->getTable() . '.' . $deployments->getLocalKeyName());
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
        $this->assertEquals($editor['reference'], $editors->getRelated()->getTable() . '.' . $editors->getForeignKeyName());
        $this->assertEquals($editor['inverse_of'], $editors->getForeignKeyName());
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsHasOneThrough(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldAuthor = $merge->firstWhere('field', 'author');
        $author = $forestModel->getModel()->author();
        $this->assertNotNull($fieldAuthor);
        $this->assertEquals($fieldAuthor['relationship'], $forestModel->mapRelationships(HasOneThrough::class));
        $this->assertEquals($fieldAuthor['field'], 'author');
        $this->assertEquals($fieldAuthor['reference'], $author->getRelated()->getTable() . '.' . $author->getLocalKeyName());
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsMorphOne(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $image = $merge->firstWhere('field', 'image');
        $images = $forestModel->getModel()->image();
        $this->assertNotNull($image);
        $this->assertEquals($image['relationship'], $forestModel->mapRelationships(MorphOne::class));
        $this->assertEquals($image['field'], 'image');
        $this->assertEquals($image['reference'], $images->getRelated()->getTable() . '.' . $images->getForeignKeyName());
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsMorphMany(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldTag = $merge->firstWhere('field', 'tags');
        $tags = $forestModel->getModel()->tags();
        $this->assertNotNull($fieldTag);
        $this->assertEquals($fieldTag['relationship'], $forestModel->mapRelationships(MorphMany::class));
        $this->assertEquals($fieldTag['field'], 'tags');
        $this->assertEquals($fieldTag['reference'], $tags->getRelated()->getTable() . '.' . $tags->getForeignKeyName());
    }

    /**
     * @return void
     */
    public function testMergeFieldsWithRelationsMorphToMany(): void
    {
        [$forestModel, $fields] = $this->makeForestModel();
        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $fieldBuy = $merge->firstWhere('field', 'buys');
        $buys = $forestModel->getModel()->buys();
        $this->assertNotNull($fieldBuy);
        $this->assertEquals($fieldBuy['relationship'], $forestModel->mapRelationships(MorphToMany::class));
        $this->assertEquals($fieldBuy['field'], 'buys');
        $this->assertEquals($fieldBuy['inverse_of'], $buys->getRelatedPivotKeyName());
    }

    /**
     * @throws Exception
     * @throws SchemaException
     * @return void
     */
    public function testSetName(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'name';
        $forestModel->setName($value);

        $this->assertEquals($value, $forestModel->getName());
    }

    /**
     * @throws Exception
     * @throws SchemaException
     * @return void
     */
    public function testSetOldName(): void
    {
        $forestModel = m::mock(ForestModel::class, [$this->getLaravelModel()])
            ->makePartial();
        $value = 'old-name';
        $forestModel->setOldName($value);

        $this->assertEquals($value, $forestModel->getOldName());
    }

    /**
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @throws Exception
     * @throws SchemaException
     * @return void
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
     * @return object
     * @throws Exception
     * @throws SchemaException
     */
    public function getLaravelModel()
    {
        $schemaManager = $this->prophesize(AbstractSchemaManager::class);
        $schemaManager->listTableColumns(Argument::any(), Argument::any())
            ->willReturn(
                [
                    'id'  => new Column('id', Type::getType('bigint')),
                    'foo' => new Column('foo', Type::getType('string')),
                ]
            );

        $connection = $this->prophesize(Connection::class);
        $connection->getTablePrefix()
            ->shouldBeCalled()
            ->willReturn('prefix.');
        $connection->getDoctrineSchemaManager()
            ->willReturn($schemaManager->reveal());

        $model = $this->prophesize(Model::class);
        $model
            ->getConnection()
            ->shouldBeCalled()
            ->willReturn($connection->reveal());
        $model
            ->getTable()
            ->shouldBeCalledOnce()
            ->willReturn('dummy_tables');

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
