<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

/**
 * Class Field
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Field
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
    protected ?string $default_value;

    /**
     * @var string|null
     */
    protected ?string $description;

    /**
     * @var string|null
     */
    protected ?string $reference;

    /**
     * @var string|null
     */
    protected ?string $hook;

    /**
     * @var array|null
     */
    protected ?array $enums;

    /**
     * @var mixed|null
     */
    protected mixed $value;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->field = $attributes['field'];
        $this->type = $attributes['type'];
        $this->is_required = $attributes['is_required'] ?? false;
        $this->is_read_only = $attributes['is_read_only'] ?? false;
        $this->default_value = $attributes['default_value'] ?? null;
        $this->reference = $attributes['reference'] ?? null;
        $this->description = $attributes['description'] ?? null;
        $this->hook = $attributes['hook'] ?? null;
        $this->value = $attributes['value'] ?? null;

        //--- required only if type === 'Enum' ---//
        $this->enums = $attributes['enums'] ?? null;
    }

    /**
     * @param mixed|null $value
     *
     * @return void
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }


    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'field'         => $this->field,
            'type'          => $this->type,
            'is_required'   => $this->is_required,
            'is_read_only'  => $this->is_read_only,
            'default_value' => $this->default_value,
            'reference'     => $this->reference,
            'description'   => $this->description,
            'hook'          => $this->hook,
            'enums'         => $this->enums,
            'value'         => $this->value,
        ];
    }
}
