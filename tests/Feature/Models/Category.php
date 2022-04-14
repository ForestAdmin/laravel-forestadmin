<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature\Models;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartSegment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Category extends Model
{
    use ForestCollection;

    protected $fillable = [
        'label',
        'product_id',
    ];

    /**
     * @return SmartSegment
     */
    public function bestCategories(): SmartSegment
    {
        return $this->smartSegment(
            fn(Builder $query) => $query->where('id', '<', 3),
            'bestName'
        );
    }
}
