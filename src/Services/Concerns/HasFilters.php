<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Doctrine\DBAL\Exception;
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
        'equal',
        'not_equal',
        'present',
        'blank',
        'contains',
        'not_contains',
        'in',
        'greater_than',
        'less_than',
        'starts_with',
        'ends_with',
    ];

    /**
     * @var array
     */
    protected array $basicSymbols = [
        'equal'        => '=',
        'not_equal'    => '!=',
        'greater_than' => '>',
        'less_than'    => '<',
    ];

    /**
     * @var array
     */
    protected array $dateOperators = [
        'after_x_hours_ago',
        'before_x_hours_ago',
        'future',
        'past',
        'previous_month_to_date',
        'previous_month',
        'previous_quarter_to_date',
        'previous_quarter',
        'previous_week_to_date',
        'previous_week',
        'previous_x_days_to_date',
        'previous_x_days',
        'previous_year_to_date',
        'previous_year',
        'today',
        'yesterday',
    ];

    /**
     * @var array
     */
    protected array $typeFieldsOperators = [
        'Boolean'  => [
            'equal',
            'not_equal',
            'present',
            'blank'
        ],
        'Date'     => [
            'equal',
            'not_equal',
            'before',
            'after',
            'present',
            'blank',
            'today',
            'yesterday',
            'previous_x_days',
            'previous_week',
            'previous_month',
            'previous_quarter',
            'previous_year',
            'previous_x_days_to_date',
            'previous_week_to_date',
            'previous_month_to_date',
            'previous_quarter_to_date',
            'previous_year_to_date',
            'past',
            'future',
            'before_x_hours_ago',
            'after_x_hours_ago',
        ],
        'Dateonly' => [
            'equal',
            'not_equal',
            'before',
            'after',
            'present',
            'blank',
            'today',
            'yesterday',
            'previous_x_days',
            'previous_week',
            'previous_month',
            'previous_quarter',
            'previous_year',
            'previous_x_days_to_date',
            'previous_week_to_date',
            'previous_month_to_date',
            'previous_quarter_to_date',
            'previous_year_to_date',
            'past',
            'future',
            'before_x_hours_ago',
            'after_x_hours_ago',
        ],
        'Enum'     => [
            'equal',
            'not_equal',
            'present',
            'blank',
            'in',
        ],
        'Number'   => [
            'equal',
            'not_equal',
            'greater_than',
            'less_than',
            'present',
            'blank',
        ],
        'String'   => [
            'equal',
            'not_equal',
            'starts_with',
            'ends_with',
            'contains',
            'not_contains',
            'present',
            'blank',
        ],
        'Uuid'     => [
            'equal',
            'not_equal',
            'present',
            'blank'
        ],
        'Time'     => [
            'equal',
            'not_equal',
            'greater_than',
            'less_than',
            'present',
            'blank',
        ],
        'Json'     => [
            'present',
            'blank',
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
     * @throws Exception
     */
    protected function handleFilter(Builder $query, array $filter, ?string $aggregator): void
    {
        $aggregator = $aggregator ?? 'and';
        if (!in_array($aggregator, $this->aggregators, true)) {
            throw new ForestException("Unsupported operator: $aggregator");
        }

        [$field, $operator, $value] = array_values($filter);
        $value = trim($value);

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

        switch ($operator) {
            case 'present':
            case 'blank':
                $query->where(
                    function ($query) use ($field, $type, $operator) {
                        $query->whereNull($field);
                        if (!in_array($type, ['Boolean', 'Uuid'], true)) {
                            $symbol = $operator === 'blank' ? '=' : '!=';
                            $query->orWhere($field, $symbol, '');
                        }
                    },
                    null,
                    null,
                    $aggregator
                );
                break;
            case 'contains':
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", ['%' . $value . '%'], $aggregator);
                break;
            case 'not_contains':
                $query->whereRaw("LOWER ($field) NOT LIKE LOWER(?)", ['%' . $value . '%'], $aggregator);
                break;
            case 'starts_with':
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", [$value . '%'], $aggregator);
                break;
            case 'ends_with':
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", ['%' . $value], $aggregator);
                break;
            case 'in':
                $value = explode(',', str_replace(' ', '', $value));
                $query->whereIn($field, $value, $aggregator);
                break;
            case 'equal':
            case 'not_equal':
            case 'greater_than':
            case 'less_than':
                if (($type === 'Number' && !is_numeric($value)) || ($type === 'Uuid' && !$this->isUuid($value))) {
                    return;
                }
                $query->where($field, $this->basicSymbols[$operator], $value, $aggregator);
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
