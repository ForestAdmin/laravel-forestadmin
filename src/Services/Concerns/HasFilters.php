<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\ForestSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
     * @var \DateTimeZone
     */
    protected \DateTimeZone $timezone;

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
        'Boolean'  => ['equal', 'not_equal', 'present', 'blank',],
        'Date'     => ['equal', 'not_equal', 'present', 'blank',],
        'Dateonly' => ['equal', 'not_equal', 'present', 'blank',],
        'Enum'     => ['equal', 'not_equal', 'present', 'blank', 'in',],
        'Number'   => ['equal', 'not_equal', 'greater_than', 'less_than', 'present', 'blank', 'in',],
        'String'   => ['equal', 'not_equal', 'starts_with', 'ends_with', 'contains', 'not_contains', 'present', 'blank', 'in',],
        'Uuid'     => ['equal', 'not_equal', 'present', 'blank'],
        'Time'     => ['equal', 'not_equal', 'greater_than', 'less_than', 'present', 'blank',],
        'Json'     => ['present', 'blank',],
    ];

    /**
     * @param Builder $query
     * @param string  $payload
     * @return void
     * @throws Exception
     * @throws \JsonException
     */
    protected function appendFilters(Builder $query, string $payload): void
    {
        [$aggregator, $filters] = $this->parseFilters($payload);
        $this->setAggregator($aggregator);

        $query->where(function($q) use ($filters) {
            foreach ($filters as $filter) {
                $this->handleFilter($q, $filter);
            }
        });
    }

    /**
     * @param Builder $query
     * @param array   $filters
     * @return void
     * @throws Exception
     */
    protected function appendScope(Builder $query, array $filters): void
    {
        $this->setAggregator($filters['aggregator']);

        $query->where(function($q) use ($filters) {
            foreach ($filters['conditions'] as $filter) {
                $this->handleFilter($q, $filter);
            }
        });
    }

    /**
     * @param Builder $query
     * @param array   $filter
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    protected function handleFilter(Builder $query, array $filter): void
    {
        [$field, $operator, $value] = array_values($filter);
        $value = is_string($value) ? trim($value) : $value;

        if (Str::contains($field, ':')) {
            $parseField = explode(':', $field);
            $relationName = $parseField[0];
            [$field, $type] = $this->getTypeByField($this->model->$relationName()->getRelated(), $parseField[1]);
            $query->whereHas(
                $relationName,
                function ($query) use ($field, $type, $operator, $value) {
                    $this->callFilter($query, $field, $type, $operator, $value);
                }
            );
        } else {
            [$field, $type] = $this->getTypeByField($this->model, $field);
            $this->callFilter($query, $field, $type, $operator, $value);
        }
    }

    /**
     * @param Builder $query
     * @param string  $field
     * @param string  $type
     * @param string  $operator
     * @param         $value
     * @return void
     * @throws \Exception
     */
    protected function callFilter(Builder $query, string $field, string $type, string $operator, $value): void
    {
        if (!$this->isOperatorValidToFieldType($type, $operator)) {
            throw new ForestException("The operator $operator is not allowed to the field type : $type");
        }

        $smartFields = ForestSchema::getSmartFields(strtolower(class_basename($this->model)));
        if (isset($smartFields[Str::after($field, '.')])) {
            $smartField = $smartFields[Str::after($field, '.')];
            call_user_func($this->model->{$smartField['field']}()->filter, $query, $value, $operator, $this->aggregator);
        } elseif (in_array($type, ['Date', 'Dateonly'], true) && in_array($operator, $this->dateOperators, true)) {
            $this->dateFilters($query, $field, $operator, $value);
        } else {
            $this->mainFilters($query, $field, $operator, $value, $type);
        }
    }

    /**
     * @param Builder $query
     * @param string  $field
     * @param string  $operator
     * @param         $value
     * @param string  $type
     * @return Builder
     */
    public function mainFilters(Builder $query, string $field, string $operator, $value, string $type): Builder
    {
        switch ($operator) {
            case 'present':
            case 'blank':
                $query->where(
                    function ($query) use ($field, $type, $operator) {
                        $operator === 'blank' ? $query->whereNull($field) : $query->whereNotNull($field);
                        if (!in_array($type, ['Boolean', 'Uuid', 'Json', 'Date', 'Dateonly', 'Number'], true)) {
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

        return $query;
    }

    /**
     * @param Builder     $query
     * @param string      $field
     * @param string      $operator
     * @param string|null $value
     * @return Builder
     * @throws \Exception
     */
    public function dateFilters(Builder $query, string $field, string $operator, ?string $value = null): Builder
    {
        switch ($operator) {
            case 'today':
                $query->whereBetween(
                    $field,
                    [
                        Carbon::now($this->timezone)->startOfDay(),
                        Carbon::now($this->timezone)->endOfDay(),
                    ]
                );
                break;
            case 'before':
                $query->where($field, '<', new Carbon(new \DateTime($value), $this->timezone));
                break;
            case 'after':
                $query->where($field, '>', new Carbon(new \DateTime($value), $this->timezone));
                break;
            case 'previous_x_days':
                $this->ensureIntegerValue($value);
                $query->whereBetween(
                    $field,
                    [
                        Carbon::now($this->timezone)->subDays($value)->startOfDay(),
                        Carbon::now($this->timezone)->subDay()->endOfDay(),
                    ]
                );
                break;
            case 'previous_x_days_to_date':
                $this->ensureIntegerValue($value);
                $query->whereBetween(
                    $field,
                    [
                        Carbon::now($this->timezone)->subDays($value)->startOfDay(),
                        Carbon::now($this->timezone)->endOfDay(),
                    ]
                );
                break;
            case 'past':
                $query->where($field, '<=', Carbon::now());
                break;
            case 'future':
                $query->where($field, '>=', Carbon::now());
                break;
            case 'before_x_hours_ago':
                $this->ensureIntegerValue($value);
                $query->where($field, '<', Carbon::now($this->timezone)->subHours($value));
                break;
            case 'after_x_hours_ago':
                $this->ensureIntegerValue($value);
                $query->where($field, '>', Carbon::now($this->timezone)->subHours($value));
                break;
            case 'yesterday':
            case 'previous_week':
            case 'previous_month':
            case 'previous_quarter':
            case 'previous_year':
            case 'previous_week_to_date':
            case 'previous_month_to_date':
            case 'previous_quarter_to_date':
            case 'previous_year_to_date':
                $period = $operator === 'yesterday' ? 'Day' : Str::ucfirst(Str::of($operator)->explode('_')->get(1));
                $sub = 'sub' . $period;
                $start = 'startOf' . $period;
                $end = 'endOf' . $period;
                if (Str::endsWith($operator, 'to_date')) {
                    $interval = [Carbon::now($this->timezone)->$start(), Carbon::now()];
                } else {
                    $interval = [Carbon::now($this->timezone)->$sub()->$start(), Carbon::now($this->timezone)->$sub()->$end()];
                }
                $query->whereBetween($field, $interval);
                break;
        }

        return $query;
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
            $aggregator = 'and';
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
        $field = $model->getTable() . '.' . $field;

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

        if (in_array($type, ['Date', 'Dateonly'], true)) {
            return in_array($operator, $this->typeFieldsOperators[$type], true) || in_array($operator, $this->dateOperators, true);
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
        if (!array_key_exists($type, $this->typeFieldsOperators)) {
            throw new ForestException("Unknown type: $type");
        }

        switch ($type) {
            case 'Number':
                return is_numeric($value);
            case 'Uuid':
                return $this->isUuid($value);
            case 'Date':
            case 'Dateonly':
            case 'Time':
                return (bool) strtotime($value);
            default:
                return true;
        }
    }

    /**
     * @param string $aggregator
     * @return $this
     */
    public function setAggregator(string $aggregator): self
    {
        if (!in_array($aggregator, $this->aggregators, true)) {
            throw new ForestException("Unsupported operator: $aggregator");
        }
        $this->aggregator = $aggregator;

        return $this;
    }
}
