<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories\BookStoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Bookstore
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Bookstore extends Model
{
    use HasFactory;

    /**
     * @return BookStoreFactory
     */
    protected static function newFactory()
    {
        return new BookStoreFactory();
    }
}
