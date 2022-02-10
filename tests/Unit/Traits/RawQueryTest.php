<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\Concerns\RawQuery;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class RawQueryTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class RawQueryTest extends TestCase
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateQueryEmptyQueryException(): void
    {
        $trait = $this->getObjectForTrait(RawQuery::class);
        $this->setProtectedProperty($trait, 'rawQuery', '');

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 You cannot execute an empty SQL query.');
        $this->invokeMethod($trait, 'validateQuery');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateQueryChainQueryException(): void
    {
        $query = 'SELECT * FROM books; SELECT * FROM categories;';
        $trait = $this->getObjectForTrait(RawQuery::class);
        $this->setProtectedProperty($trait, 'rawQuery', $query);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 You cannot chain SQL queries.');
        $this->invokeMethod($trait, 'validateQuery');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateQueryMethodNotAllowedQueryException(): void
    {
        $query = 'INSERT INTO books (label, category_id)VALUES (foo, 1);';
        $trait = $this->getObjectForTrait(RawQuery::class);
        $this->setProtectedProperty($trait, 'rawQuery', $query);

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 Only SELECT queries are allowed.');
        $this->invokeMethod($trait, 'validateQuery');
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateQuery(): void
    {
        // to review
        $query = 'SELECT * FROM books';
        $trait = $this->getObjectForTrait(RawQuery::class);
        $this->setProtectedProperty($trait, 'rawQuery', $query);

        $result = $this->invokeMethod($trait, 'validateQuery');
        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAbortIfException(): void
    {
        $trait = $this->getObjectForTrait(RawQuery::class);
        $data = collect(
            ['key' => 'foo_key',  'item' => 'foo_value']
        );

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('🌳🌳🌳 The result columns must be named \'key, value\' instead of \'key,item\'');
        $this->invokeMethod($trait, 'abortIf', [true, $data, "key, value"]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAbortIf(): void
    {
        // to review
        $trait = $this->getObjectForTrait(RawQuery::class);
        $data = collect(
            ['key' => 'foo_key',  'item' => 'foo_value']
        );

        $result = $this->invokeMethod($trait, 'abortIf', [false, $data, "key, item"]);
        $this->assertNull($result);
    }
}
