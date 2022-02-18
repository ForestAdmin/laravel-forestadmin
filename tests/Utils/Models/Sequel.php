<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Sequel
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Sequel extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'sequelable_type',
        'sequelable_id',
    ];

    /**
     * @return MorphTo
     */
    public function sequelable(): MorphTo
    {
        return $this->morphTo();
    }
}
