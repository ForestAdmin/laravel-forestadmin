<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature\Models;

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
    protected $fillable = [
        'label',
        'product_id',
    ];
}
