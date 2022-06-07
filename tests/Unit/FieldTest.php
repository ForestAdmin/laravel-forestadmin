<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartActionField;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class FieldTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class FieldTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetField(): void
    {
        $field = new SmartActionField(['field' => 'foo', 'type' => 'string', 'is_required' => true, 'is_read_only' => false, 'value' => 'foo']);

        $this->assertEquals('foo', $field->getField());
    }

    /**
     * @return void
     */
    public function testGetHook(): void
    {
        $field = new SmartActionField(['field' => 'foo', 'type' => 'string', 'is_required' => true, 'is_read_only' => false, 'hook' => 'onFooChange']);

        $this->assertEquals('onFooChange', $field->getHook());
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMerge(): void
    {
        $field = new SmartActionField(['field' => 'foo', 'type' => 'string', 'is_required' => true, 'is_read_only' => false, 'value' => 'foo']);

        $data = ['value' => 'new value'];
        $field->merge($data);

        $this->assertEquals('new value', $this->invokeProperty($field, 'value'));
    }

    /**
     * @return void
     */
    public function testValidEnum(): void
    {
        $field = new SmartActionField(['field' => 'foo', 'type' => 'Enum', 'is_required' => true, 'enums' => [1, 2, 3]]);

        $this->assertTrue($field->validEnum());
    }

    /**
     * @return void
     */
    public function testValidEnumException(): void
    {
        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 You must add enums choices on your field foo');

        new SmartActionField(['field' => 'foo', 'type' => 'Enum', 'is_required' => true]);
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $field = new SmartActionField(['field' => 'foo', 'type' => 'string', 'is_required' => true, 'is_read_only' => false, 'value' => 'foo']);
        $serialize = $field->serialize();

        $result = [
            'field'         => 'foo',
            'type'          => 'String',
            'is_required'   => true,
            'is_read_only'  => false,
            'default_value' => null,
            'reference'     => null,
            'description'   => null,
            'hook'          => null,
            'enums'         => null,
            'value'         => 'foo',
        ];

        $this->assertEquals($result, $serialize);
    }
}
