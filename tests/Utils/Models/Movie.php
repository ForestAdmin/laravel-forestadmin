<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Movie
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Movie extends Model
{
    use HasFactory;
    use ForestCollection;



    protected $fillable = [
        'body',
        'book_id',
    ];

    /**
     * @return SmartRelationship
     */
    public function smartCategory(): SmartRelationship
    {
        return $this->smartRelationship(
            [
                'type' => 'String',
                'reference' => 'category.id'
            ]
        )
            ->get(
                function () {
                    return Category::select('categories.*')
                        ->join('books', 'books.category_id', '=', 'categories.id')
                        ->where('books.id', $this->book_id)
                        ->first();
                }
            );
    }

    /**
     * @return BelongsTo
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
