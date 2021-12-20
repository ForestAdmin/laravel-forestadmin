<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use App\Models\Book;
use Doctrine\DBAL\Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * Class HasManyGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyGetter extends ResourceGetter
{
    /**
     * @var string
     */
    protected string $relation;

    /**
     * @var string
     */
    protected string $relationName;

    /**
     * @var Model
     */
    private Model $parentInstance;


    /**
     * @param Model  $model
     * @param string $name
     * @param string $relation
     * @param string $relationName
     * @param        $parentId
     */
    public function __construct(Model $model, string $name, string $relation, string $relationName, $parentId)
    {
        parent::__construct($model, $name);
        $this->relation = $relation;
        $this->relationName = $relationName;
        $this->parentInstance = $this->model->find($parentId);
    }


    /**
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function all(): LengthAwarePaginator
    {
        $relatedModel = $this->parentInstance->{$this->relation}()->getRelated();
        $params = $this->params['fields'] ?? [];
        $queryFields = $params[Str::camel(class_basename($relatedModel))] ?? null;
        $pageParams = $this->params['page'] ?? [];

        $records = $this->parentInstance->{$this->relation}()->paginate(
            $pageParams['size'] ?? null,
            $this->handleFields($relatedModel, $queryFields),
            'page',
            $pageParams['number'] ?? null
        );

        if ($this->parentInstance->{$this->relation}() instanceof BelongsToMany) {
            $records->getCollection()->transform(
                function ($value) {
                    return $value->unsetRelations('pivot');
                }
            );
        }
        return $records;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->parentInstance->{$this->relation}()->count();
    }
}
