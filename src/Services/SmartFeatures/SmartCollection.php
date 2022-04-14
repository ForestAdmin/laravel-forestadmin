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
        $this->isValid();

        return $this->fields()->map(fn($item) => $item->serialize())->all();
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
            'segments'               => [],
        ];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $filter = $this->fields()->filter(fn($item) => $item instanceof SmartField ? null : $item);

        if (!empty($filter->all())) {
            throw new ForestException("Each field of a SmartCollection must be an instance of SmartField");
        }

        return true;
    }
}
