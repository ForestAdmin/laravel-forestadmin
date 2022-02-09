<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\Concerns;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use Illuminate\Support\Collection;
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
     */
    private function validateQuery(): void
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

    /**
     * @param bool       $condition
     * @param Collection $result
     * @param string     $keyNames
     * @return void
     */
    protected function abortIf(bool $condition, Collection $result, string $keyNames): void
    {
        if ($condition) {
            $resultKeys = $result->keys()->implode(',');
            throw new ForestException("The result columns must be named '$keyNames' instead of '$resultKeys'");
        }
    }
}
