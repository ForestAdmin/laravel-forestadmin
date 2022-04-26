<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use Doctrine\DBAL\Types\Type;
use ForestAdmin\LaravelForestAdmin\Services\Concerns\DatabaseHelper;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class DatabaseHelperTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class DatabaseHelperTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    public function testGetColumns(): void
    {
        $trait = $this->getObjectForTrait(DatabaseHelper::class);
        $result = $this->invokeMethod($trait, 'getColumns', [Book::first()]);
        $keyExpected = [
            'id',
            'label',
            'comment',
            'difficulty',
            'amount',
            'active',
            'options',
            'other',
            'category_id',
            'published_at',
            'sold_at',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($keyExpected, array_keys($result));
        $this->assertEquals(Type::getType('integer'), $result['id']->getType());
        $this->assertEquals(Type::getType('string'), $result['label']->getType());
        $this->assertEquals(Type::getType('float'), $result['amount']->getType());
        $this->assertEquals(Type::getType('boolean'), $result['active']->getType());
        $this->assertEquals(Type::getType('text'), $result['options']->getType());
        $this->assertEquals(Type::getType('datetime'), $result['created_at']->getType());
    }
}
