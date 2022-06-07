<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class SmartRelationshipTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartRelationshipTest extends TestCase
{
    /**
     * @return void
     */
    public function testGet(): void
    {
        $smartField = new SmartRelationship(['field' => 'fooRelation', 'type' => 'String', 'reference' => 'foo.id']);
        $smartField->get(fn() => 'foo');
        $resultCall = call_user_func($smartField->get);

        $this->assertEquals('foo', $resultCall);
    }

    /**
     * @return void
     */
    public function testGetRelated(): void
    {
        $smartField = new SmartRelationship(['field' => 'fooRelation', 'type' => 'String', 'reference' => 'foo.id']);
        $smartField->get(fn() => 'foo');
        $result = $smartField->getRelated();

        $this->assertEquals('foo', $result);
    }
}
