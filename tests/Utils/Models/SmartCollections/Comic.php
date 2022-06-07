<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\SmartCollections;

use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartCollection;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
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
}
