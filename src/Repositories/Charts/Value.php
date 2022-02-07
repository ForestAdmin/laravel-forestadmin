<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories\Charts;

use ForestAdmin\LaravelForestAdmin\Repositories\ChartRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Value
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Value extends ChartRepository
{
    public function __construct(Model $model)
    {
        dd('value');
        parent::__construct($model);
    }
}
