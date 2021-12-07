<?php

namespace ForestAdmin\LaravelForestAdmin\Schema\Concerns;

use Illuminate\Support\Collection;

/**
 * Class HasIncludes
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait HasIncludes
{
    /**
     * @var Collection
     */
    protected Collection $includes;

    /**
     * @return Collection
     */
    public function getIncludes(): Collection
    {
        return $this->includes;
    }

    /**
     * @param string      $key
     * @param string      $relationTable
     * @param array       $fields
     * @param string|null $foreignKey
     * @return $this
     */
    protected function addInclude(string $key, string $relationTable, array $fields, ?string $foreignKey = null): self
    {
        $this->includes->put(
            $key,
            [
                'relation_table' => $relationTable,
                'fields'         => implode(',', $fields),
                'foreign_key'    => $foreignKey
            ]
        );

        return $this;
    }
}
