<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Advertisement
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'book_id',
    ];
}
