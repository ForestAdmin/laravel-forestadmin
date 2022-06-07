<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Comment
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Comment extends Model
{
    protected $fillable = [
        'body',
        'user_id',
        'book_id',
    ];

    /**
     * @return BelongsTo
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
