<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartActions;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;

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

    /**::
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

        if ($this->type === 'Enum') {
            $this->validEnum();
        }
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string|null
     */
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * @param array $data
     * @return Field
     */
    public function merge(array $data): Field
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function validEnum(): bool
    {
        if (!is_array($this->enums)) {
            throw new ForestException('You must add enums choices on your field ' . $this->field);
        }

        return true;
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
