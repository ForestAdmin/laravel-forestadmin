<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Tag
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Tag extends Model
{
    /**
     * @return MorphTo
     */
    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
