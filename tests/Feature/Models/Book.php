<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature\Models;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartActions\SmartAction;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\RequestBulk;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

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
     * @return Collection
     */
    public function smartActions(): Collection
    {
        return collect(
            [
            App::makeWith(
                SmartAction::class,
                [
                    'model'   => class_basename($this),
                    'name'    => 'smart action bulk',
                    'type'    => 'bulk',
                    'execute' => function () {
                        $ids = $this->getIdsFromBulkRequest();
                        return ['success' => "ids => " . implode(',', $ids)];
                    },
                ]
            ),
            App::makeWith(
                SmartAction::class,
                [
                    'model'   => class_basename($this),
                    'name'    => 'smart action single',
                    'type'    => 'single',
                    'execute' => function () {

                        return ['success' => "Test working!"];
                    },
                ]
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
                ),
            ]
        );
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
