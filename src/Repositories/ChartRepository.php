<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChartRepository
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartRepository extends BaseRepository
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->params = request()->all();
        parent::__construct($model);
    }
}
