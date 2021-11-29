<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Utils;

use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\Database;

/**
 * Class DatabaseTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class DatabaseTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetSource(): void
    {
        $mysql = 'mysql';
        $this->assertEquals($mysql, Database::getSource($mysql));

        $postgres = 'pgsql';
        $this->assertEquals('postgres', Database::getSource($postgres));

        $sqlserver = 'sqlsrv';
        $this->assertEquals('mssql', Database::getSource($sqlserver));
    }

    /**
     * @return void
     */
    public function testGetSourceException(): void
    {
        $foo = 'foo';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The database dialect `$foo` is not supported");

        Database::getSource($foo);
    }
}
