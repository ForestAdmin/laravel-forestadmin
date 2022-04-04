<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartAction;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartField;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
     * @param $name
     * @return mixed|void
     */
    public function getSmartAction($name)
    {
        $smartActions = ForestSchema::getSmartActions(strtolower(class_basename($this)));
        foreach ($smartActions as $smartAction) {
            if (Str::slug($smartAction['name']) === $name && method_exists($this, $smartAction['methodName'])) {
                return $smartAction;
            }
        }

        throw new ForestException("There is no smart-action $name");
    }

    /**
     * @param string      $type
     * @param \Closure    $execute
     * @param string|null $name
     * @return SmartAction
     */
    public function smartAction(string $type, \Closure $execute, ?string $name = null): SmartAction
    {
        [$one, $field] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $methodName = $field['function'];
        $name = $name ?? $methodName;

        return new SmartAction(class_basename($this), $name, $type, $execute, $methodName);
    }

    /**
     * @param array $attributes
     * @return SmartRelationship
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
