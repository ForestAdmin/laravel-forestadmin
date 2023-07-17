<?php

namespace ForestAdmin\LaravelForestAdmin\Utils;

class Database
{
    /**
     * @param string $name
     * @return string
     */
    public static function getSource(string $name): string
    {
        if (! in_array($name, ['sqlite', 'mysql', 'pgsql', 'sqlsrv'], true)) {
            throw new \RuntimeException("The database dialect `$name` is not supported");
        }

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
