<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\SmartCollections;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\ForestCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Category;
use Illuminate\Support\Collection;

/**
 * Class Comic
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Comic extends SmartCollection
{
    use ForestCollection;

    protected string $name = 'comic';

    protected bool $is_searchable = true;

    protected bool $is_read_only = true;

    /**
     * @return Collection
     */
    public function fields(): Collection
    {
        return collect(
            [
                new SmartField(
                    [
                        'field'       => 'id',
                        'type'        => 'Number',
                        'is_sortable' => true,
                    ]
                ),
                new SmartField(
                    [
                        'field' => 'label',
                        'type'  => 'String',
                    ]
                ),
                new SmartField(
                    [
                        'field' => 'created_at',
                        'type'  => 'DateTime',
                    ]
                ),
            ]
        );
    }

    /**
     * @return SmartRelationship
     */
    public function category(): SmartRelationship
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
                        ->where('books.id', $this->id)
                        ->first();
                }
            );
    }

    /**
     * @return SmartRelationship
     */
    public function bookStores(): SmartRelationship
    {
        return $this->smartRelationship(
            [
                'type' => ['String'],
                'reference' => 'bookStore.id'
            ]
        )
            ->get(
                function () {
                    return [];
                }
            );
    }
}
