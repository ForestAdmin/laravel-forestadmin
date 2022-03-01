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
    protected bool $is_required = false;

    /**
     * @var bool
     */
    protected bool $is_read_only = false;

    /**
     * @var string|null
     */
    protected ?string $default_value = null;

    /**
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * @var string|null
     */
    protected ?string $reference = null;

    /**
     * @var string|null
     */
    protected ?string $hook = null;

    /**
     * @var array|null
     */
    protected ?array $enums = null;

    /**
     * @param string $field
     * @param string $type
     */
    public function __construct(string $field, string $type)
    {
        $this->field = $field;
        $this->type = $type;
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
        ];
    }

    /**
     * @param bool $value
     * @return Field
     */
    public function required(bool $value = false): Field
    {
        $this->is_required = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return Field
     */
    public function readOnly(bool $value = false): Field
    {
        $this->is_read_only = $value;

        return $this;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function default(?string $value = null): Field
    {
        $this->default_value = $value;

        return $this;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function reference(?string $value = null): Field
    {
        $this->reference = $value;

        return $this;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function description(?string $value = null): Field
    {
        $this->description = $value;

        return $this;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function hook(?string $value = null): Field
    {
        $this->hook = $value;

        return $this;
    }

    /**
     * @param array|null $value
     * @return $this
     */
    public function enums(?array $value = null): Field
    {
        $this->enums = $value;

        return $this;
    }
}
