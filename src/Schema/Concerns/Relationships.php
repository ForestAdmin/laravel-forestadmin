<?php

namespace ForestAdmin\LaravelForestAdmin\Schema\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Relationships
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait Relationships
{
    /**
     * @var array
     * MorphTo not listed, we use only the field RELATION_id & RELATION_type
     * HasOneThrough & HasManyThrough not supported
     */
    protected array $doctrineTypes = [
        BelongsTo::class      => 'BelongsTo',
        BelongsToMany::class  => 'BelongsToMany',
        HasMany::class        => 'HasMany',
        //HasManyThrough::class => 'HasMany',
        HasOne::class         => 'HasOne',
        //HasOneThrough::class  => 'HasOne',
        MorphMany::class      => 'HasMany',
        MorphOne::class       => 'BelongsTo',
        MorphToMany::class    => 'HasMany',
    ];

    /**
     * @param string $type
     * @return string
     */
    protected function mapRelationships(string $type): string
    {
        return $this->doctrineTypes[$type];
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getRelations(Model $model): array
    {
        return array_reduce(
            (new \ReflectionClass($model))->getMethods(\ReflectionMethod::IS_PUBLIC),
            function ($result, \ReflectionMethod $method) {
                ($returnType = $method->getReturnType()) &&
                in_array($returnType->getName(), array_keys($this->doctrineTypes), true) &&
                ($result = array_merge($result, [$method->getName() => $returnType->getName()]));

                return $result;
            },
            []
        );
    }
}
