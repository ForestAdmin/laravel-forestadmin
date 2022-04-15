<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
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
    public function testSerializeFields(): void
    {
        $smartCollection = $this->buildSmartCollection();

        $this->assertIsArray($smartCollection->serializeFields());
        $this->assertEquals(
            [
                (new SmartField(
                    [
                        'field' => 'id',
                        'type'  => 'Number',
                    ]
                ))->serialize(),
                (new SmartField(
                    [
                        'field' => 'foo',
                        'type'  => 'String',
                    ]
                ))->serialize(),
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
        $smartCollection = $this->buildSmartCollection();
        $this->invokeProperty($smartCollection, 'name', 'MyCustomSmartCollection');

        $this->assertIsArray($smartCollection->serialize());
        $this->assertEquals(
            [
                "name"                   => "MyCustomSmartCollection",
                "name_old"               => "MyCustomSmartCollection",
                "icon"                   => null,
                "is_read_only"           => false,
                "is_virtual"             => true,
                "is_searchable"          => false,
                "only_for_relationships" => false,
                "pagination_type"        => "page",
                "fields"                 => [
                    (new SmartField(
                        [
                            'field' => 'id',
                            'type'  => 'Number',
                        ]
                    ))->serialize(),
                    (new SmartField(
                        [
                            'field' => 'foo',
                            'type'  => 'String',
                        ]
                    ))->serialize(),
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
        $smartCollection = m::mock(SmartCollection::class)->makePartial();
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
            ->getMock();

        return $smartCollection;
    }
}
