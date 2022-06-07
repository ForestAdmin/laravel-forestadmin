<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories\RangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Range
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Range extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'label',
    ];

    /**
     * @return RangeFactory
     */
    protected static function newFactory()
    {
        return new RangeFactory();
    }

    /**
     * @return BelongsToMany
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
