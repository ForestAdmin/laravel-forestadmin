<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Closure;
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

    /**
     * @param Builder $query
     * @param array   $includes
     * @return Builder
     * @throws \ReflectionException
     */
//    protected function appendRelations(Builder $query, array $includes): Builder
//    {
//        /** @var \Closure $closure */
//        $eagerLoads = $query->getEagerLoads();
//        foreach ($includes as $key => $include) {
//            if ($include['foreign_key']) {
//                $query->addSelect($include['foreign_key']);
//            }
//
//            if (isset($eagerLoads[$key])) {
//                $this->setIncludeEagerLoad($query, $eagerLoads[$key], $key, $include);
//            }
//
//            $query->with($key . ':' . $include['fields']);
//        }
//
//        return $query;
//    }

    protected function appendRelations(Builder $query, array $includes): Builder
    {
        /** @var \Closure $closure */
        $eagerLoads = $query->getEagerLoads();
        foreach ($includes as $key => $include) {
            if ($include['foreign_key']) {
                $query->addSelect($include['foreign_key']);
            }

            if (isset($eagerLoads[$key])) {
                $with = (new ReflectionFunction($eagerLoads[$key]))->getStaticVariables();
                /** @var TYPE_NAME $query */
                $relationTable = $query->getModel()->$key()->getRelated()->getTable();
                if (! empty($with)) {
                    if (isset($with['name'])) {
                        $include = $this->mergerIncludeFields($with, $include, $key, $relationTable);
                    } elseif (isset($with['constraints'])) {
                        $with = (new ReflectionFunction($with['constraints'][0]))->getStaticVariables();
                        /** @var TYPE_NAME $query */
                        if (!empty($with) && isset($with['name'])) {
                            $include = $this->mergerIncludeFields($with, $include, $key, $relationTable);
                        } else {
                            $include['fields'] = "$relationTable.*";
                        }
                    } else {
                        $include['fields'] = "$relationTable.*";
                    }
                } else {
                    $include['fields'] = "$relationTable.*";
                }

                $query->with($key . ':' . $include['fields']);
            }
        }

        return $query;
    }

    private function mergerIncludeFields(array $with, array $include, string $key, string $relationTable): array
    {
        $fieldsRelationEagerLoad = explode(',', Str::after($with['name'], "$key:"));
        $includeFields = explode(',', Str::replace("$relationTable.", '', $include['fields']));
        $include['fields'] = collect($fieldsRelationEagerLoad)
            ->merge($includeFields)
            ->map(fn($field) => "$relationTable.$field")
            ->implode(',');

        return $include;
    }

}
