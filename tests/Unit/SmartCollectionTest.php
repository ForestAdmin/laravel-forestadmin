<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\SmartCollections\Comic;
use Illuminate\Support\Collection;
use Mockery as m;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class SmartCollectionTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartCollectionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @return void
     */
    public function testFields(): void
    {
        $smartCollection = new SmartCollection();

        $this->assertInstanceOf(Collection::class, $smartCollection->fields());
    }

    /**
     * @return void
     */
    public function testGetName(): void
    {
        $smartCollection = new Comic();

        $this->assertEquals('comic', $smartCollection->getName());
    }

    /**
     * @return void
     */
    public function testSerializeFields(): void
    {
        $smartCollection = new Comic();

        $this->assertIsArray($smartCollection->serializeFields());
        $this->assertEquals(
            [
                (new SmartField(
                    [
                        'field'       => 'id',
                        'type'        => 'Number',
                        'is_sortable' => true,
                    ]
                ))->serialize(),
                (new SmartField(
                    [
                        'field' => 'label',
                        'type'  => 'String',
                    ]
                ))->serialize(),
                (new SmartField(
                    [
                        'field' => 'created_at',
                        'type'  => 'DateTime',
                    ]
                ))->serialize(),
                [
                    "field"         => "category",
                    "type"          => "String",
                    "default_value" => null,
                    "enums"         => null,
                    "integration"   => null,
                    "is_filterable" => false,
                    "is_read_only"  => false,
                    "is_required"   => false,
                    "is_sortable"   => false,
                    "is_virtual"    => true,
                    "reference"     => "category.id",
                    "inverse_of"    => null,
                    "validations"   => [],
                ],
                [
                    "field"         => "bookStores",
                    "type"          => ["String"],
                    "default_value" => null,
                    "enums"         => null,
                    "integration"   => null,
                    "is_filterable" => false,
                    "is_read_only"  => false,
                    "is_required"   => false,
                    "is_sortable"   => false,
                    "is_virtual"    => true,
                    "reference"     => "bookStore.id",
                    "inverse_of"    => null,
                    "validations"   => [],
                ],
            ],
            $smartCollection->serializeFields()
        );
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testSerialize(): void
    {
        $smartCollection = new Comic();

        $this->assertIsArray($smartCollection->serialize());
        $this->assertEquals(
            [
                "name"                   => "comic",
                "name_old"               => "comic",
                "class"                  => Comic::class,
                "icon"                   => null,
                "is_read_only"           => true,
                "is_virtual"             => true,
                "is_searchable"          => true,
                "only_for_relationships" => false,
                "pagination_type"        => "page",
                "fields"                 => [
                    (new SmartField(
                        [
                            'field'       => 'id',
                            'type'        => 'Number',
                            'is_sortable' => true,
                        ]
                    ))->serialize(),
                    (new SmartField(
                        [
                            'field' => 'label',
                            'type'  => 'String',
                        ]
                    ))->serialize(),
                    (new SmartField(
                        [
                            'field' => 'created_at',
                            'type'  => 'DateTime',
                        ]
                    ))->serialize(),
                    [
                        "field"         => "category",
                        "type"          => "String",
                        "default_value" => null,
                        "enums"         => null,
                        "integration"   => null,
                        "is_filterable" => false,
                        "is_read_only"  => false,
                        "is_required"   => false,
                        "is_sortable"   => false,
                        "is_virtual"    => true,
                        "reference"     => "category.id",
                        "inverse_of"    => null,
                        "validations"   => [],
                    ],
                    [
                        "field"         => "bookStores",
                        "type"          => ["String"],
                        "default_value" => null,
                        "enums"         => null,
                        "integration"   => null,
                        "is_filterable" => false,
                        "is_read_only"  => false,
                        "is_required"   => false,
                        "is_sortable"   => false,
                        "is_virtual"    => true,
                        "reference"     => "bookStore.id",
                        "inverse_of"    => null,
                        "validations"   => [],
                    ],
                ],
                "actions"                => [],
                "segments"               => [],
            ],
            $smartCollection->serialize()
        );
    }

    /**
     * @return void
     */
    public function testIsValid(): void
    {
        $smartCollection = $this->buildSmartCollection();

        $this->assertTrue($smartCollection->isValid());
    }

    /**
     * @return void
     */
    public function testHydrate(): void
    {
        $item = [
            'id'              => 1,
            'label'           => 'foo',
            'created_at'      => '1970-01-01 00:00:00',
            'undefined_field' => null,
        ];
        $comic = Comic::hydrate($item);

        $this->assertInstanceOf(Comic::class, $comic);
        $this->assertObjectHasAttribute('id', $comic);
        $this->assertObjectHasAttribute('label', $comic);
        $this->assertObjectHasAttribute('created_at', $comic);
        $this->assertObjectNotHasAttribute('undefined_field', $comic);
        $this->assertEquals(1, $comic->id);
        $this->assertEquals('foo', $comic->label);
        $this->assertEquals('1970-01-01 00:00:00', $comic->created_at);
    }

    /**
     * @return void
     */
    public function testIsValidException(): void
    {
        $smartCollection = m::mock(SmartCollection::class)->makePartial();
        $smartCollection->shouldReceive('fields')
            ->andReturn(
                collect(
                    [
                        'field' => 'id',
                    ]
                )
            )
            ->getMock();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Each field of a SmartCollection must be an instance of SmartField');

        $smartCollection->isValid();
    }

    /**
     * @return m\Mock
     */
    public function buildSmartCollection()
    {
        $smartCollection = m::mock(Comic::class)->makePartial();
        $smartCollection->shouldReceive('fields')
            ->andReturn(
                collect(
                    [
                        new SmartField(
                            [
                                'field' => 'id',
                                'type'  => 'Number',
                            ]
                        ),
                        new SmartField(
                            [
                                'field' => 'foo',
                                'type'  => 'String',
                            ]
                        ),
                    ]
                )
            )
            ->shouldReceive()
            ->getMock();

        return $smartCollection;
    }
}
