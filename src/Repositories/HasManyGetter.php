<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use App\Models\Book;
use Doctrine\DBAL\Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
        $pageParams = $this->params['page'] ?? [];
        $relatedModel = $this->model->{$this->relation}()->getRelated();
        return $this->buildQuery($relatedModel, $this->relationName)->paginate(
            $pageParams['size'] ?? null,
            '*',
            'page',
            $pageParams['number'] ?? null
        );
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->parentInstance->{$this->relation}()->count();
    }
}
