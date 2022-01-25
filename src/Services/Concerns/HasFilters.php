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
     * @var string
     */
    protected string $aggregator;

    /**
     * @var string|null
     */
    protected ?string $timezone;

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
        'today',
        'yesterday',
        'before',
        'after',
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
            'present',
            'blank',
        ],
        'Dateonly' => [
            'equal',
            'not_equal',
            'present',
            'blank',
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
            'in',
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
            'in',
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
     * @param Builder $query
     * @param string  $payload
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    protected function appendFilters(Builder $query, string $payload)
    {
        [$aggregator, $filters] = $this->parseFilters($payload);
        $this->setAggregator($aggregator);

        foreach ($filters as $filter) {
            $this->handleFilter($query, $filter);
        }
    }

    /**
     * @param Builder $query
     * @param array   $filter
     * @return void
     * @throws Exception
     */
    protected function handleFilter(Builder $query, array $filter): void
    {
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

        if (in_array($type, ['Date', 'Dateonly'], true) && in_array($operator, $this->dateOperators, true)) {
            $this->dateFilters($query, $field, $operator, $value);
        } else {
            $this->mainFilters($query, $field, $operator, $value, $type);
        }
    }

    /**
     * @param Builder $query
     * @param string  $field
     * @param string  $operator
     * @param string  $value
     * @param string  $type
     * @return void
     */
    public function mainFilters(Builder $query, string $field, string $operator, string $value, string $type)
    {
        switch ($operator) {
            case 'present':
            case 'blank':
                $query->where(
                    function ($query) use ($field, $type, $operator) {
                        $operator === 'blank' ? $query->whereNull($field) : $query->whereNotNull($field);
                        if (!in_array($type, ['Boolean', 'Uuid', 'Json', 'Date', 'Dateonly'], true)) {
                            $symbol = $operator === 'blank' ? '=' : '!=';
                            $query->orWhere($field, $symbol, '');
                        }
                    },
                    null,
                    null,
                    $this->aggregator
                );
                break;
            case 'contains':
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", ['%' . $value . '%'], $this->aggregator);
                break;
            case 'not_contains':
                $query->whereRaw("LOWER ($field) NOT LIKE LOWER(?)", ['%' . $value . '%'], $this->aggregator);
                break;
            case 'starts_with':
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", [$value . '%'], $this->aggregator);
                break;
            case 'ends_with':
                $query->whereRaw("LOWER ($field) LIKE LOWER(?)", ['%' . $value], $this->aggregator);
                break;
            case 'in':
                $value = array_map('trim', explode(',', $value));
                if ($type === 'Number' && !is_numeric($value)) {
                    $value = collect($value)->reject(fn($item) => !is_numeric($item))->all();
                }
                $query->whereIn($field, $value, $this->aggregator);
                break;
            case 'equal':
            case 'not_equal':
            case 'greater_than':
            case 'less_than':
                if (!$this->validateValue($value, $type)) {
                    throw new ForestException(
                        "The type of value '$value' is not compatible with the type: $type"
                    );
                }
                $query->where($field, $this->basicSymbols[$operator], $value, $this->aggregator);
                break;
            default:
                throw new ForestException(
                    "Unsupported operator: $operator"
                );
        }
    }

    /**
     * @param Builder $query
     * @param string  $field
     * @param string  $operator
     * @param string  $value
     * @return void
     */
    public function dateFilters(Builder $query, string $field, string $operator, string $value)
    {
        //
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

    /**
     * @param $value
     * @param $type
     * @return bool
     */
    public function validateValue($value, $type): bool
    {
        if (! in_array($type, array_keys($this->typeFieldsOperators), true)) {
            throw new ForestException("Unknown type: $type");
        }

        switch ($type) {
            case 'Number':
                return is_numeric($value);
            case 'Uuid':
                return $this->isUuid($value);
            case 'Date':
            case 'Dateonly':
                return (bool) strtotime($value);
            default:
                return true;
        }
    }

    /**
     * @param string|null $aggregator
     * @return $this
     */
    public function setAggregator(?string $aggregator): self
    {
        $aggregator = $aggregator ?? 'and';
        if (!in_array($aggregator, $this->aggregators, true)) {
            throw new ForestException("Unsupported operator: $aggregator");
        }
        $this->aggregator = $aggregator;

        return $this;
    }
}
