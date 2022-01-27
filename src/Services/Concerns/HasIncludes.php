<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;

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
     * @var array
     */
    protected array $includes = [];

    /**
     * @return array
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * @param Builder $query
     * @param array   $includes
     * @return Builder
     */
    protected function appendRelations(Builder $query, array $includes): Builder
    {
        foreach ($includes as $key => $value) {
            if ($value['foreign_key']) {
                $query->addSelect($value['foreign_key']);
            }
            $query->with($key . ':' . $value['fields']);
        }

        return $query;
    }

    /**
     * @param string      $key
     * @param array       $fields
     * @param string|null $foreignKey
     * @return $this
     */
    protected function addInclude(string $key, array $fields, ?string $foreignKey = null): self
    {
        $this->includes[$key] = [
            'fields'         => implode(',', $fields),
            'foreign_key'    => $foreignKey
        ];

        return $this;
    }
}
