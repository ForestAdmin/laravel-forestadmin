<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HasSearch
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait HasSearch
{
    /**
     * @param Builder $query
     * @param         $search
     * @param bool    $isExtended
     * @return void
     */
    protected function appendSearch(Builder $query, $search, bool $isExtended = false): void
    {
        $model = $query->getModel();
        $singleRelations = $this->getSingleRelations($model);
        $query->where(
            function ($query) use ($search, $model, $isExtended, $singleRelations) {
                if ($isExtended && !empty($singleRelations)) {
                    foreach ($singleRelations as $key => $value) {
                        $relatedModel = $model->$key()->getRelated();
                        $fieldsToSearch = $this->getFieldsToSearch($relatedModel);
                        $query->orWhereHas(
                            $key,
                            function ($query) use ($fieldsToSearch, $search, $relatedModel) {
                                $query->where(
                                    function ($query) use ($fieldsToSearch, $search, $relatedModel) {
                                        foreach ($fieldsToSearch as $field) {
                                            $this->handleSearchField($query, $relatedModel, $field, $search);
                                        }
                                    }
                                );
                            }
                        );
                    }
                } else {
                    $fieldsToSearch = $this->getFieldsToSearch($model);
                    foreach ($fieldsToSearch as $field) {
                        $this->handleSearchField($query, $model, $field, $search);
                    }
                }
            }
        );
    }

    /**
     * @param Builder $query
     * @param Model   $model
     * @param array   $field
     * @param string  $value
     * @return Builder
     */
    protected function handleSearchField(Builder $query, Model $model, array $field, string $value): Builder
    {
        $name = $model->getTable() . '.' . $field['field'];
        if ($field['is_virtual']) {
            $query = call_user_func($model->{$field['field']}()->search, $query, $value);
        } elseif ($field['type'] === 'Number') {
            if (is_numeric($value)) {
                $query->orWhere($name, (int) $value);
            }
        } elseif ($field['type'] === 'Enum' || $this->isUuid($value)) {
            $query->orWhere($name, $value);
        } else {
            $query->orWhereRaw("LOWER ($name) LIKE LOWER(?)", ['%' . $value . '%']);
        }

        return $query;
    }

    /**
     * @param Model $model
     * @return array
     */
    protected function getFieldsToSearch(Model $model): array
    {
        $fieldsToSearch = [];
        $fields = ForestSchema::getFields(class_basename($model));
        foreach ($fields as $field) {
            if (in_array($field['type'], ['String', 'Number', 'Enum'], true) && !$field['reference'] && $this->fieldInSearchFields($model, $field['field'])) {
                $fieldsToSearch[] = $field;
            }
        }

        return $fieldsToSearch;
    }

    /**
     * @param Model  $model
     * @param string $field
     * @return bool
     */
    protected function fieldInSearchFields(Model $model, string $field): bool
    {
        return method_exists($model, 'searchFields') === false
            || empty($model->searchFields())
            || in_array($field, $model->searchFields(), true);
    }
}
