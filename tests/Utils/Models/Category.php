<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartSegment;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Category
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Category extends Model
{
    use HasFactory;
    use ForestCollection;

    /**
     * @var string[]
     */
    protected $fillable = [
        'label',
        'product_id',
    ];

    /**
     * @return CategoryFactory
     */
    protected static function newFactory()
    {
        return new CategoryFactory();
    }

    /**
     * @return HasMany
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

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
