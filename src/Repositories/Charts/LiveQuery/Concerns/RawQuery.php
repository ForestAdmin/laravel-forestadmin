<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\Concerns;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use Illuminate\Support\Str;

/**
 * Trait RawQuery
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
trait RawQuery
{
    /**
     * @var string
     */
    protected string $rawQuery;

    /**
     * @return void
     * @throws ForestException
     */
    protected function validateQuery(): void
    {
        if (empty($this->rawQuery)) {
            throw new ForestException('You cannot execute an empty SQL query.');
        }

        if (Str::contains($this->rawQuery, ';') && (strpos($this->rawQuery, ';') < (strlen($this->rawQuery) - 1))) {
            throw new ForestException('You cannot chain SQL queries.');
        }

        if (! preg_match('/\ASELECT\s.*FROM\s.*\z/im', $this->rawQuery)) {
            throw new ForestException('Only SELECT queries are allowed.');
        }
    }
}
