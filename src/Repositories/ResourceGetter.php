<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Schema\Concerns\HasQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ResourceGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceGetter extends BaseRepository
{
    use HasQuery;

    /**
     * @var array
     */
    protected array $params;

    /**
     * @param Model  $model
     * @param string $name
     */
    public function __construct(Model $model, string $name)
    {
        $this->params = request()->query();
        parent::__construct($model, $name);
    }

    /**
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function all(): LengthAwarePaginator
    {
        $pageParams = $this->params['page'] ?? [];
        return $this->query()->paginate(
            $pageParams['size'] ?? null,
            '*',
            'page',
            $pageParams['number'] ?? null
        );
    }

    /**
     * @param $id
     * @return Model
     * @throws Exception
     */
    public function get($id): Model
    {
        $resource = $this->query()->find($id);
        if (!$resource) {
            $this->throwException('Collection not found');
        }

        return $resource;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * @return Builder
     * @throws Exception
     */
    protected function query(): Builder
    {
        return $this->buildQuery($this->model, $this->name);
    }
}