<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class Buy
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Buy extends Model
{
    /**
     * @return MorphToMany
     */
    public function books(): MorphToMany
    {
        return $this->morphedByMany(Book::class, 'buyable');
    }
}
