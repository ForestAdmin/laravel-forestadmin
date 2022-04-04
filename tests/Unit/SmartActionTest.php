<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartActionField;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Support\Facades\App;

/**
 * Class SmartActionTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartActionTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetKey(): void
    {
        $key = $this->buildSmartAction()->getKey();

        $this->assertEquals('smart-action', $key);
    }

    /**
     * @return void
     */
    public function testGetFields(): void
    {
        $fields = $this->buildSmartAction()->getFields();
        $result = [
            'foo' => [
                'field'         => 'foo',
                'type'          => 'String',
                'is_required'   => true,
                'is_read_only'  => false,
                'default_value' => null,
                'reference'     => null,
                'description'   => null,
                'hook'          => 'onFooChange',
                'enums'         => null,
                'value'         => null,
            ],
            'bar' => [
                'field'         => 'bar',
                'type'          => 'String',
                'is_required'   => true,
                'is_read_only'  => false,
                'default_value' => null,
                'reference'     => null,
                'description'   => null,
                'hook'          => null,
                'enums'         => null,
                'value'         => null,
            ],
        ];

        $this->assertEquals($result, $fields);
    }

    /**
     * @return void
     */
    public function testGetField(): void
    {
        $field = $this->buildSmartAction()->getField('foo');

        $this->assertInstanceOf(SmartActionField::class, $field);
        $this->assertEquals('foo', $field->getField());
    }

    /**
     * @return void
     */
    public function testGetFieldException(): void
    {
        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 There is no field TEST in your smart-action');

        $this->buildSmartAction()->getField('TEST');
    }

    /**
     * @return void
     */
    public function testGetExecute(): void
    {
        $execute = $this->buildSmartAction()->getExecute();

        $this->assertInstanceOf(\Closure::class, $execute);
    }

    /**
     * @return void
     */
    public function testGetLoad(): void
    {
        $load = $this->buildSmartAction()->getLoad();

        $this->assertInstanceOf(\Closure::class, $load);
    }

    /**
     * @return void
     */
    public function testGetChange(): void
    {
        $change = $this->buildSmartAction()->getChange('foo');

        $this->assertInstanceOf(\Closure::class, $change);
    }

    /**
     * @return void
     */
    public function testGetChangeException(): void
    {
        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 There is no hook on the field bar');

        $this->buildSmartAction()->getChange('bar');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testDownload(): void
    {
        $smartAction = $this->buildSmartAction();
        $smartAction->download(true);
        $download = $this->invokeProperty($smartAction, 'download');

        $this->assertTrue($download);
    }

    /**
     * @return void
     */
    public function testHooks(): void
    {
        $hooks = $this->buildSmartAction()->hooks();

        $this->assertIsArray($hooks);
        $this->assertEquals(['load' => true, 'change' => ['onFooChange']], $hooks);
    }

    /**
     * @return void
     */
    public function testMergeRequestFields(): void
    {
        $smartAction = $this->buildSmartAction();
        $smartAction->mergeRequestFields(
            [
                ['field' => 'foo', 'type' => 'string', 'is_required' => true, 'hook' => 'onFooChange', 'value' => 'foo data'],
                ['field' => 'bar', 'type' => 'string', 'is_required' => true],
            ]
        );
        $field = $smartAction->getField('foo');

        $this->assertEquals('foo data', $this->invokeProperty($field, 'value'));
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $serialize = $this->buildSmartAction()->serialize();
        $result = [
            'id'         => 'SmartActionTest.smart action',
            'name'       => 'smart action',
            'methodName' => 'smartAction',
            'fields'     => [
                [
                    'field'         => 'foo',
                    'type'          => 'String',
                    'is_required'   => true,
                    'is_read_only'  => false,
                    'default_value' => null,
                    'reference'     => null,
                    'description'   => null,
                    'hook'          => 'onFooChange',
                    'enums'         => null,
                    'value'         => null,
                ],
                [
                    'field'         => 'bar',
                    'type'          => 'String',
                    'is_required'   => true,
                    'is_read_only'  => false,
                    'default_value' => null,
                    'reference'     => null,
                    'description'   => null,
                    'hook'          => null,
                    'enums'         => null,
                    'value'         => null,
                ],
            ],
            'endpoint'   => '/forest/smart-actions/smartactiontest_smart-action',
            'type'       => 'single',
            'download'   => false,
            'hooks'      => [
                'load'   => true,
                'change' => ['onFooChange'],
            ],
        ];

        $this->assertEquals($result, $serialize);
    }

    /**
     * @return mixed
     */
    public function buildSmartAction()
    {
        return App::makeWith(
            SmartAction::class,
            [
                'model'   => class_basename($this),
                'name'    => 'smart action',
                'type'    => 'single',
                'execute' => function () {
                    return ['success' => true];
                },
                'methodName' => 'smartAction',
            ]
        )
            ->load(
                function () {
                    $fields = $this->getFields();
                    $fields['foo']['value'] = 'default';

                    return $fields;
                }
            )
            ->change(
                [
                    'onFooChange' => function () {
                        $fields = $this->getFields();
                        $fields['foo']['value'] = 'Test onChange Foo';

                        return $fields;
                    },
                ]
            )
            ->addField(['field' => 'foo', 'type' => 'string', 'is_required' => true, 'hook' => 'onFooChange'])
            ->addField(['field' => 'bar', 'type' => 'string', 'is_required' => true]);
    }
}
