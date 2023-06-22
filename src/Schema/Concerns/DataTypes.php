<?php

namespace ForestAdmin\LaravelForestAdmin\Schema\Concerns;

use Doctrine\DBAL\Types\Types;

/**
 * Class DataTypes
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait DataTypes
{
    /**
     * @var array
     */
    protected array $dbTypes = [
        Types::ARRAY                => 'array',
        Types::ASCII_STRING         => 'String',
        Types::BIGINT               => 'Number',
        Types::BINARY               => 'unknown',
        Types::BLOB                 => 'unknown',
        Types::BOOLEAN              => 'Boolean',
        Types::DATE_MUTABLE         => 'DateOnly',
        Types::DATE_IMMUTABLE       => 'DateOnly',
        Types::DATEINTERVAL         => 'unknown',
        Types::DATETIME_MUTABLE     => 'Date',
        Types::DATETIME_IMMUTABLE   => 'Date',
        Types::DATETIMETZ_MUTABLE   => 'Date',
        Types::DATETIMETZ_IMMUTABLE => 'Date',
        Types::DECIMAL              => 'Number',
        Types::FLOAT                => 'Number',
        Types::GUID                 => 'Uuid',
        Types::INTEGER              => 'Number',
        Types::JSON                 => 'Json',
        Types::OBJECT               => 'unknown',
        Types::SIMPLE_ARRAY         => 'array',
        Types::SMALLINT             => 'Number',
        Types::STRING               => 'String',
        Types::TEXT                 => 'String',
        Types::TIME_MUTABLE         => 'Time',
        Types::TIME_IMMUTABLE       => 'Time',
        'timestamp'                 => 'Date',
    ];

    /**
     * @param string $dbType
     * @return string
     */
    public function getType(string $dbType): string
    {
        return $this->dbTypes[$dbType];
    }
}
