<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class HasFilters
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait HasFilters
{
    /**
     * @var array
     */
    protected array $aggregators = [
        'and',
        'or',
    ];

    /**
     * @var array
     */
    protected array $operators = [
        'blank'       => 'blank',
        'contains'    => 'contains',
        'endsWith'    => 'ends_with',
        'equal'       => 'equal',
        'greaterThan' => 'greater_than',
        'in'          => 'in',
        'includesAll' => 'includes_all',
        'lessThan'    => 'less_than',
        'notContains' => 'not_contains',
        'notEqual'    => 'not_equal',
        'notIn'       => 'not_in',
        'present'     => 'present',
        'startsWith'  => 'starts_with',
    ];

    /**
     * @var array
     */
    protected array $dateOperators = [
        'afterXHoursAgo',
        'beforeXHoursAgo',
        'future',
        'past',
        'previousMonthToDate',
        'previousMonth',
        'previousQuarterToDate',
        'previousQuarter',
        'previousWeekToDate',
        'previousWeek',
        'previousXDaysToDate',
        'previousXDays',
        'previousYearToDate',
        'previousYear',
        'today',
        'yesterday',
    ];

    /**
     * @var array
     */
    protected array $typeFieldsOperators = [
        'String'   => [
            'blank',
            'present',
            'equal',
            'notEqual',
            'in',
            'notIn',
            'contains',
            'notContains',
            'endsWith',
            'startsWith',
        ],
        'Number'   => [
            'present',
            'equal',
            'notEqual',
            'greaterThan',
            'notIn',
            'lessThan',
        ],
        'Boolean'  => [
            'equal',
            'notEqual',
            'blank'
        ],
        'Dateonly' => [
            'equal',
            'notEqual',
            'present',
            'blank',
            'today',
            'yesterday',
            'previousXDaysToDate',
            'previousWeek',
            'previousWeekToDate',
            'previousMonth',
            'previousMonthToDate',
            'previousQuarterToDate',
            'previousQuarter',
            'previousYear',
            'previousYearToDate',
            'past',
            'future',
            'previousXDays',
        ],
        'Date'     => [
            'equal',
            'notEqual',
            'present',
            'blank',
            'today',
            'yesterday',
            'previousXDaysToDate',
            'previousWeek',
            'previousWeekToDate',
            'previousMonth',
            'previousMonthToDate',
            'previousQuarterToDate',
            'previousQuarter',
            'previousYear',
            'previousYearToDate',
            'past',
            'future',
            'previousXDays',
            'beforeXHoursAgo',
            'afterXHoursAgo',
        ],
        'Timeonly' => [
            'equal',
            'notEqual',
            'lessThan',
            'greaterThan',
            'present',
            'blank',
        ],
        'Enum'     => [
            'equal',
            'notEqual',
            'present',
            'blank',
            'in',
            'notIn',
        ],
        'Json'     => [
            'present',
            'blank',
        ],
        'Point'    => [
            'equal'
        ],
        'Uuid'     => [
            'equal',
            'notEqual',
            'present',
            'blank'
        ],
    ];

    /**
     * @param string $type
     * @return string
     */
    public function getOperator(string $type): string
    {
        return $this->operators[$type];
    }

    /**
     * @param Builder $query
     * @param string  $payload
     * @return void
     * @throws \JsonException
     */
    protected function appendFilters(Builder $query, string $payload)
    {
        [$aggregator, $filters] = $this->parseFilters($payload);
        foreach ($filters as $filter) {
            $this->handleFilter($query, $filter, $aggregator);
        }
    }

    /**
     * @param Builder     $query
     * @param array       $filter
     * @param string|null $aggregator
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    protected function handleFilter(Builder $query, array $filter, ?string $aggregator): void
    {
        $aggregator = $aggregator ?? 'and';
        if (!in_array($aggregator, $this->aggregators, true)) {
            throw new ForestException("Unsupported operator: $aggregator");
        }

        [$field, $operator, $value] = array_values($filter);

        if (Str::contains($field, ':')) {
            $parseField = explode(':', $field);
            $relationName = $parseField[0];
            $this->appendIncludes($query, $this->handleWith($this->model, [$relationName => $parseField[1]]));
            [$field, $type] = $this->getTypeByField($this->model->$relationName()->getRelated(), $parseField[1]);
        } else {
            [$field, $type] = $this->getTypeByField($this->model, $field);
        }

        if (!$this->isOperatorValidToFieldType($type, $operator)) {
            throw new ForestException("The operator $operator is not allowed to the field type : $type");
        }

        // DATES OPERATORS PR FINIR


        //$query->where('id', 1);
        //dd($field, $operator, $value);
        //, string $operator, $value


        switch ($operator) {
            case $this->operators['blank']:
                $query->where(
                    function ($query) use ($field, $type) {
                        $query->whereNull($field);
                        if (in_array($type, ['Boolean', 'Uuid'], true)) {
                            $query->orWhere($field, '=', '');
                        }
                    },
                    null,
                    null,
                    $aggregator
                );
                break;
            case $this->operators['present']:
                $query->where(
                    fn($query) => $query->whereNotNull($field)->orWhere($field, '!=', ''),
                    null,
                    null,
                    $aggregator
                );
                break;
            case $this->operators['contains']:
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", ['%' . $value . '%'], $aggregator);
                break;
            case $this->operators['notContains']:
                $query->whereRaw("LOWER ($field) NOT LIKE LOWER(?)", ['%' . $value . '%'], $aggregator);
                break;
            case $this->operators['startsWith']:
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", [$value . '%'], $aggregator);
                break;
            case $this->operators['endsWith']:
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", ['%' . $value], $aggregator);
                break;
            case $this->operators['lessThan']:
                $query->where($field, '<', $value, $aggregator);
                break;
            case $this->operators['equal']:
                $query->where($field, '=', $value, $aggregator);
                break;
            case $this->operators['notEqual']:
                $query->where($field, '!=', $value, $aggregator);
                break;
            case $this->operators['greaterThan']:
                $query->where($field, '>', $value, $aggregator);
                break;
            case $this->operators['in']:
                $query->whereIn($field, $value, $aggregator);
                break;
            case $this->operators['notIn']:
                $query->whereIn($field, $value, $aggregator, true);
                break;
            case $this->operators['includesAll']:
                foreach ($value as $data) {
                    $query->whereIn($field, $data, $aggregator);
                }
                break;
            default:
                throw new ForestException(
                    "Unsupported operator: $operator"
                );
        }
    }

    /**
     * @param string $data
     * @return array
     * @throws \JsonException
     */
    public function parseFilters(string $data): array
    {
        $dataToArray = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('aggregator', $dataToArray)) {
            $aggregator = $dataToArray['aggregator'];
            $filters = $dataToArray['conditions'];
        } else {
            $aggregator = null;
            $filters[] = $dataToArray;
        }

        return [$aggregator, $filters];
    }

    /**
     * @param Model  $model
     * @param string $field
     * @return array
     */
    public function getTypeByField(Model $model, string $field): array
    {
        $type = ForestSchema::getTypeByField(class_basename($model), $field);
        $field = $this->model->getTable() . '.' . $field;

        if (is_null($type)) {
            throw new ForestException("Unknown field $field for this collection");
        }

        return [$field, $type];
    }

    /**
     * @param string $type
     * @param string $operator
     * @return bool
     */
    public function isOperatorValidToFieldType(string $type, string $operator): bool
    {
        if (!array_key_exists($type, $this->typeFieldsOperators)) {
            throw new ForestException("Field type unknown: $type");
        }

        return in_array($operator, $this->typeFieldsOperators[$type], true);
    }
}
