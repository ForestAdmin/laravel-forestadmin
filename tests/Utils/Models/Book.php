<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\RequestBulk;
use Illuminate\Database\Eloquent\Builder;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class Book
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Book extends Model
{
    use ForestCollection;
    use RequestBulk;
    use HasFactory;

    protected $casts = [
        'options' => 'array',
        'active'  => 'boolean',
    ];

    protected $fillable = [
        'label',
        'comment',
        'difficulty',
        'amount',
        'options',
        'category_id',
        'published_at',
    ];

    /**
     * @return SmartField
     */
    public function reference(): SmartField
    {
        return $this->smartField(['type' => 'String'])
            ->get(fn() => $this->label . '-' . $this->difficulty)
            ->set(
                function ($value) {
                    $data = explode('-', $value);
                    $this->label = $data[0];
                    $this->difficulty = $data[1];

                    return $this;
                }
            )
            ->sort(fn(Builder $query, string $direction) => $query->orderBy('label', $direction))
            ->filter(
                static function (Builder $query, $value, string $operator, string $aggregator) {
                    $data = explode('-', $value);
                    if ($operator === 'equal') {
                        return $query->where('label', $data[0])
                            ->where('difficulty', $data[1]);
                    }

                    return $query;
                }
            );
    }

    /**
     * @return SmartRelationship
     */
    public function smartBookstores(): SmartRelationship
    {
        return $this->smartRelationship(
            [
                'type' => ['String'],
                'reference' => 'bookstore.id'
            ]
        )
            ->get(
                static function ($id) {
                    return Bookstore::select('bookstores.*')
                        ->leftJoin('companies', 'companies.id', '=', 'bookstores.company_id')
                        ->leftJoin('books', 'companies.book_id', '=', 'books.id')
                        ->where('books.id', $id)
                        ->paginate();
                }
            );
    }

    /**
     * @return SmartAction
     */
    public function smartActionBulk(): SmartAction
    {
        return $this->smartAction(
            'bulk',
            'smart action bulk',
            function () {
                $ids = $this->getIdsFromBulkRequest();
                return ['success' => "ids => " . implode(',', $ids)];
            },
        );
    }

    /**
     * @return SmartAction
     */
    public function smartActionSingle(): SmartAction
    {
        return $this->smartAction(
            'bulk',
            'smart action single',
            fn () => ['success' => "Test working!"],
        )
            ->addField(['field' => 'token', 'type' => 'string', 'is_required' => true])
            ->addField(['field' => 'foo', 'type' => 'string', 'is_required' => true, 'hook' => 'onFooChange'])
            ->load(
                function () {
                    $fields = $this->getFields();
                    $fields['token']['value'] = 'default';

                    return $fields;
                }
            )
            ->change(
                [
                    'onFooChange' => function () {
                        $fields = $this->getFields();
                        $fields['token']['value'] = 'Test onChange Foo';

                        return $fields;
                    }
                ]
            );
    }

    /**
     * @return BookFactory
     */
    protected static function newFactory()
    {
        return new BookFactory();
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany
     */
    public function ranges(): BelongsToMany
    {
        return $this->belongsToMany(Range::class);
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany
     */
    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }

    /**
     * @return HasManyThrough
     */
    public function bookstores(): HasManyThrough
    {
        return $this->hasManyThrough(Company::class, Bookstore::class);
    }

    /**
     * @return HasOne
     */
    public function editor(): HasOne
    {
        return $this->hasOne(Editor::class);
    }

    /**
     * @return HasOne
     */
    public function advertisement(): HasOne
    {
        return $this->hasOne(Advertisement::class);
    }

    /**
     * @return HasOneThrough
     */
    public function authorUser(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Author::class);
    }

    /**
     * @return MorphOne
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    /**
     * @return MorphMany
     */
    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    /**
     * @return MorphToMany
     */
    public function buys(): MorphToMany
    {
        return $this->morphToMany(Buy::class, 'buyable');
    }

    /**
     * @return MorphMany
     */
    public function sequels(): MorphMany
    {
        return $this->morphMany(Sequel::class, 'sequelable');
    }
}
