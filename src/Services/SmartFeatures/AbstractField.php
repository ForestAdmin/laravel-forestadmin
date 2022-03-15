<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;

/**
 * Class AbstractField
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class AbstractField implements FieldContract
{
    /**
     * @var string
     */
    protected string $field;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string|null
     */
    protected ?string $default_value;

    /**
     * @var bool
     */
    protected bool $is_required;

    /**
     * @var bool
     */
    protected bool $is_read_only;

    /**
     * @var string|null
     */
    protected ?string $reference;

    /**
     * @var array|null
     */
    protected ?array $enums;

    /**
     * @var string|null
     */
    protected ?string $integration;

    /**
     * @var bool
     */
    protected bool $is_filterable;

    /**
     * @var bool
     */
    protected bool $is_sortable;

    /**
     * @var bool
     */
    protected bool $is_virtual;

    /**
     * @var string|null
     */
    protected ?string $inverse_of;

    /**
     * @var array
     */
    protected array $validations;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->field = $attributes['field'];
        $this->type = $attributes['type'];
        $this->is_required = $attributes['is_required'] ?? false;
        $this->is_read_only = $attributes['is_read_only'] ?? false;
        $this->reference = $attributes['reference'] ?? null;
        $this->default_value = null;
        $this->enums = $attributes['enums'] ?? null;

        // TODO check if useless
        $this->integration = null;
        $this->is_filterable = true;
        $this->is_sortable = true;
        $this->is_virtual = true;
        $this->inverse_of = null;
        $this->validations = [];
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'field'         => $this->field,
            'type'          => $this->type,
            'default_value' => $this->default_value,
            'enums'         => $this->enums,
            'integration'   => $this->integration,
            'is_filterable' => $this->is_filterable,
            'is_read_only'  => $this->is_read_only,
            'is_required'   => $this->is_required,
            'is_sortable'   => $this->is_sortable,
            'is_virtual'    => $this->is_virtual,
            'reference'     => $this->reference,
            'inverse_of'    => $this->inverse_of,
            'validations'   => $this->validations,
        ];
    }
}
