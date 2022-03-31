<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use Closure;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

/**
 * Class SmartCollection
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartCollection
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var bool
     */
    protected bool $is_read_only = false;

    /**
     * @var bool
     */
    protected bool $is_searchable = false;

    /**
     * @return Collection
     */
    public function fields(): Collection
    {
        return collect();
    }

    /**
     * @return array
     */
    public function serializeFields(): array
    {
        return $this->fields()->mapWithKeys(
            function ($item) {
                $data = $item->serialize();
                return [$data['field'] => $data];
            }
        )->all();
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'name'                   => $this->name,
            'name_old'               => $this->name,
            'icon'                   => null,
            'is_read_only'           => $this->is_read_only,
            'is_virtual'             => true,
            'is_searchable'          => $this->is_searchable,
            'only_for_relationships' => false,
            'pagination_type'        => 'page',
            'fields'                 => $this->serializeFields(),
            'actions'                => [],
        ];
    }
}
