<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use ReflectionFunction;

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
     * @throws \ReflectionException
     */
    protected function appendRelations(Builder $query, array $includes): Builder
    {
        /** @var \Closure $closure */
        $eagerLoads = $query->getEagerLoads();
        foreach ($includes as $key => $value) {
            if ($value['foreign_key']) {
                $query->addSelect($value['foreign_key']);
            }

            if (isset($eagerLoads[$key])) {
                $with = (new ReflectionFunction($eagerLoads[$key]))->getStaticVariables();
                $relation = $query->getModel()->$key();
                $relationTable = $relation->getRelated()->getTable();
                if ($with) {
                    $fieldsRelationEagerLoad = explode(',', Str::after($with['name'], "$key:"));
                    $includeFields = explode(',', Str::replace("$relationTable.", '', $value['fields']));
                    $value['fields'] = collect($fieldsRelationEagerLoad)
                        ->merge($includeFields)
                        ->map(fn ($field) => "$relationTable.$field")
                        ->implode(',');
                } else {
                    $value['fields'] = "$relationTable.*";
                }
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
