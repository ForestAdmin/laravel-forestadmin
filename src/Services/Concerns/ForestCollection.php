<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ForestCollection
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait ForestCollection
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function schemaFields(): array
    {
        return [];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function searchFields(): array
    {
        return [];
    }

    /**
     * @return Collection
     * @codeCoverageIgnore
     */
    public function smartActions(): Collection
    {
        return collect();
    }

    /**
     * @param string $name
     * @return SmartAction
     */
    public function getSmartAction(string $name): SmartAction
    {
        $smartAction = $this->smartActions()->first(
            fn ($item) => $item->getKey() === $name
        );

        if (null !== $smartAction) {
            return $smartAction;
        } else {
            throw new ForestException("There is no smart-action $name");
        }
    }

    /**
     * @param array $attributes
     * @return SmartField
     */
    public function smartField(array $attributes): SmartField
    {
        [$one, $field] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $attributes['field'] = $field['function'];


        return new SmartField($attributes);
    }

    /**
     * @param array $attributes
     * @return SmartField
     */
    public function smartRelationship(array $attributes): SmartRelationship
    {
        [$one, $field] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $attributes['field'] = $field['function'];


        return new SmartRelationship($attributes);
    }

    /**
     * @return Model
     */
    public function handleSmartFields(): Model
    {
        $smartFields = ForestSchema::getSmartFields(strtolower(class_basename($this)));
        foreach ($smartFields as $smartField) {
            $this->{$smartField['field']} = call_user_func($this->{$smartField['field']}()->get);
        }

        return $this;
    }

    /**
     * @return Model
     */
    public function handleSmartRelationships(): Model
    {
        $smartRelationships = ForestSchema::getSmartRelationships(strtolower(class_basename($this)));
        foreach ($smartRelationships as $smartRelationship) {
            //--- only belongsTo relation ---//
            if (!is_array($smartRelationship['type'])) {
                $this->setRelation($smartRelationship['field'], call_user_func($this->{$smartRelationship['field']}()->get));
            }
        }

        return $this;
    }
}
