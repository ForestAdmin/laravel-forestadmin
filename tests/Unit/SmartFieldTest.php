<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SmartFieldTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartFieldTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet(): void
    {
        $smartField = new SmartField(['field' => 'reference', 'type' => 'String']);
        $smartField->get(fn() => 'foo');
        $resultCall = call_user_func($smartField->get);

        $this->assertEquals('foo', $resultCall);
    }

    /**
     * @return void
     */
    public function testSet(): void
    {
        $book = Book::first();
        $smartField = new SmartField(['field' => 'reference', 'type' => 'String']);
        $smartField->set(
            function ($value) {
                $this->label = $value;
                return $this;
            }
        );
        // use call closure from PHP for binding model book
        $resultCall = $smartField->set->call($book, 'value');

        $this->assertInstanceOf(Book::class, $resultCall);
        $this->assertEquals(true, $resultCall->isDirty('label'));
        $this->assertEquals('value', $resultCall->label);
    }

    /**
     * @return void
     */
    public function testSort(): void
    {
        $smartField = new SmartField(['field' => 'reference', 'type' => 'String']);
        $smartField->sort(fn(Builder $query, $direction) => $query->orderBy('id', $direction));
        $resultCall = call_user_func($smartField->sort, Book::query(), 'asc');

        $this->assertInstanceOf(Builder::class, $resultCall);
        $this->assertEquals('asc', $resultCall->getQuery()->orders[0]['direction']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFilter(): void
    {
        $smartField = new SmartField(['field' => 'reference', 'type' => 'String']);
        $smartField->filter(
            fn(Builder $query, $value, string $operator, string $aggregator) => $query->where('foo', '=', $value, $aggregator)
        );
        $resultCall = call_user_func($smartField->filter, Book::query(), 'foo', 'equal', 'and');
        $expected = [
            'type'     => 'Basic',
            'column'   => 'foo',
            'operator' => '=',
            'value'    => 'foo',
            'boolean'  => 'and',
        ];

        $this->assertInstanceOf(Builder::class, $resultCall);
        $this->assertTrue($this->invokeProperty($smartField, 'is_filterable'));
        $this->assertEquals($expected, $resultCall->getQuery()->wheres[0]);
    }

    /**
     * @return void
     */
    public function testSearch(): void
    {
        $smartField = new SmartField(['field' => 'reference', 'type' => 'String']);
        $smartField->search(fn(Builder $query, $value) => $query->whereRaw("LOWER(label) LIKE LOWER(?)", ['%' . $value . '%']));
        $resultCall = call_user_func($smartField->search, Book::query(), 'foo');
        $expected = [
            'type'    => 'raw',
            'sql'     => 'LOWER(label) LIKE LOWER(?)',
            'boolean' => 'and',
        ];

        $this->assertInstanceOf(Builder::class, $resultCall);
        $this->assertEquals($expected, $resultCall->getQuery()->wheres[0]);
    }

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $smartField = new SmartField(['field' => 'reference', 'type' => 'String']);
        $result = $smartField->serialize();
        $expected = [
            'field'         => 'reference',
            'type'          => 'String',
            'default_value' => null,
            'enums'         => null,
            'integration'   => null,
            'is_filterable' => false,
            'is_read_only'  => true,
            'is_required'   => false,
            'is_sortable'   => false,
            'is_virtual'    => true,
            'reference'     => null,
            'inverse_of'    => null,
            'validations'   => [],
        ];

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }
}
