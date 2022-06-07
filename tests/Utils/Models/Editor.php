<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartSegment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Editor
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Editor extends Model
{
    use ForestCollection;

    protected $fillable = [
        'name',
        'book_id',
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
