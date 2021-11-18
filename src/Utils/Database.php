<?php

namespace ForestAdmin\LaravelForestAdmin\Utils;

/**
 * Class Database
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Database
{
    /**
     * @param string $name
     * @return string
     */
    public static function getSource(string $name): string
    {
        switch ($name) {
            case 'pgsql':
                $name = 'postgres';
                break;
            case 'sqlsrv':
                $name = 'mssql';
                break;
            default:
                $name;
        }

        return $name;
    }
}
