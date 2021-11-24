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
        $f = $forestModel->getFields();
        $fieldFoo = array_search('foo', array_column($f, 'field'), true);
        $fieldBar = array_search('bar', array_column($f, 'field'), true);
        $fieldLabel = array_search('label', array_column($f, 'field'), true);

        $this->assertIsArray($f);
        $this->assertNotNull($fieldFoo);
        $this->assertNotNull($fieldBar);
        $this->assertNotNull($fieldLabel);
        $this->assertEquals($f[$fieldBar]['type'], 'Enum');
        $this->assertEquals($f[$fieldBar]['enums'], ['easy', 'hard']);
        $this->assertArrayHasKey('default_value', $f[$fieldLabel]);
        $this->assertArrayHasKey('enums', $f[$fieldLabel]);
        $this->assertArrayHasKey('integration', $f[$fieldLabel]);
        $this->assertArrayHasKey('is_filterable', $f[$fieldLabel]);
        $this->assertArrayHasKey('is_read_only', $f[$fieldLabel]);
        $this->assertArrayHasKey('is_required', $f[$fieldLabel]);
        $this->assertArrayHasKey('is_sortable', $f[$fieldLabel]);
        $this->assertArrayHasKey('is_virtual', $f[$fieldLabel]);
        $this->assertArrayHasKey('reference', $f[$fieldLabel]);
        $this->assertArrayHasKey('widget', $f[$fieldLabel]);
        $this->assertArrayHasKey('validations', $f[$fieldLabel]);
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

        $fields = $forestModel->fetchFieldsFromTable();

        $this->assertInstanceOf(Collection::class, $fields);
        $this->assertIsArray($fields['id']);
        $this->assertArrayHasKey('field', $fields['id']);
        $this->assertArrayHasKey('type', $fields['id']);
        $this->assertArrayHasKey('is_required', $fields['id']);
        $this->assertArrayHasKey('default_value', $fields['id']);
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
     * @throws Exception
     * @throws SchemaException
     * @return void
     */
    public function testMergeFieldsWithRelations(): void
    {
        $dummyModel = new Book();
        $forestModel = m::mock(ForestModel::class, [$dummyModel])
            ->makePartial();

        $fields = collect(
            [
                'category_id' => ['field' => 'category_id'],
            ]
        );

        $relations = $forestModel->getRelations($forestModel->getModel());
        $merge = $forestModel->mergeFieldsWithRelations($fields, $relations);

        $c = $merge->firstWhere('field', 'category');
        $category = $forestModel->getModel()->category();
        $this->assertNotNull($c);
        $this->assertEquals($c['relationship'], $forestModel->mapRelationships(BelongsTo::class));
        $this->assertEquals($c['reference'], $category->getRelated()->getTable() . '.' . $category->getOwnerKeyName());
        $this->assertEquals($c['inverse_of'], $category->getOwnerKeyName());

        $r = $merge->firstWhere('field', 'ranges');
        $ranges = $forestModel->getModel()->ranges();

        $this->assertNotNull($r);
        $this->assertEquals($r['relationship'], $forestModel->mapRelationships(BelongsToMany::class));
        $this->assertEquals($r['inverse_of'], $ranges->getRelatedPivotKeyName());

        $co = $merge->firstWhere('field', 'comments');
        $comments = $forestModel->getModel()->comments();
        $this->assertNotNull($co);
        $this->assertEquals($co['relationship'], $forestModel->mapRelationships(HasMany::class));
        $this->assertEquals($co['field'], 'comments');
        $this->assertEquals($co['reference'], $comments->getRelated()->getTable() . '.' . $comments->getForeignKeyName());
        $this->assertEquals($co['inverse_of'], $comments->getForeignKeyName());

        $d = $merge->firstWhere('field', 'deployments');
        $deployments = $forestModel->getModel()->deployments();
        $this->assertNotNull($d);
        $this->assertEquals($d['relationship'], $forestModel->mapRelationships(HasManyThrough::class));
        $this->assertEquals($d['field'], 'deployments');
        $this->assertEquals($d['reference'], $deployments->getRelated()->getTable() . '.' . $deployments->getLocalKeyName());

        $e = $merge->firstWhere('field', 'editor');
        $editors = $forestModel->getModel()->editor();
        $this->assertNotNull($e);
        $this->assertEquals($e['relationship'], $forestModel->mapRelationships(HasOne::class));
        $this->assertEquals($e['field'], 'editor');
        $this->assertEquals($e['reference'], $editors->getRelated()->getTable() . '.' . $editors->getForeignKeyName());
        $this->assertEquals($e['inverse_of'], $editors->getForeignKeyName());

        $a = $merge->firstWhere('field', 'author');
        $author = $forestModel->getModel()->author();
        $this->assertNotNull($a);
        $this->assertEquals($a['relationship'], $forestModel->mapRelationships(HasOneThrough::class));
        $this->assertEquals($a['field'], 'author');
        $this->assertEquals($a['reference'], $author->getRelated()->getTable() . '.' . $author->getLocalKeyName());

        $i = $merge->firstWhere('field', 'image');
        $images = $forestModel->getModel()->image();
        $this->assertNotNull($i);
        $this->assertEquals($i['relationship'], $forestModel->mapRelationships(MorphOne::class));
        $this->assertEquals($i['field'], 'image');
        $this->assertEquals($i['reference'], $images->getRelated()->getTable() . '.' . $images->getForeignKeyName());

        $t = $merge->firstWhere('field', 'tags');
        $tags = $forestModel->getModel()->tags();
        $this->assertNotNull($t);
        $this->assertEquals($t['relationship'], $forestModel->mapRelationships(MorphMany::class));
        $this->assertEquals($t['field'], 'tags');
        $this->assertEquals($t['reference'], $tags->getRelated()->getTable() . '.' . $tags->getForeignKeyName());

        $b = $merge->firstWhere('field', 'buys');
        $buys = $forestModel->getModel()->buys();
        $this->assertNotNull($b);
        $this->assertEquals($b['relationship'], $forestModel->mapRelationships(MorphToMany::class));
        $this->assertEquals($b['field'], 'buys');
        $this->assertEquals($b['inverse_of'], $buys->getRelatedPivotKeyName());
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
    private function getLaravelModel()
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
}
