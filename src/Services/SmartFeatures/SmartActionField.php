<?php

namespace ForestAdmin\LaravelForestAdmin\Services\SmartFeatures;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;

/**
 * Class SmartActionField
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class SmartActionField extends AbstractField
{
    /**
     * @var string|null
     */
    protected ?string $description;

    /**
     * @var string|null
     */
    protected ?string $hook;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

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
     * @return string|null
     */
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * @param array $data
     * @return SmartActionField
     */
    public function merge(array $data): SmartActionField
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
