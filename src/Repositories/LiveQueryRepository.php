<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns\ChartHelper;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\Concerns\RawQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class LiveQueryRepository
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class LiveQueryRepository
{
    use ChartHelper;
    use RawQuery;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string|null
     */
    protected ?string $recordId = null;

    /**
     * Construct LiveQuery
     */
    public function __construct()
    {
        $this->rawQuery = trim(request()->input('query'));
        $this->type = request()->input('type');
        $this->recordId = request()->input('record_id');
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $this->validateQuery();

        if ($this->recordId) {
            $this->rawQuery = Str::replace('?', $this->recordId, $this->rawQuery);
        }

        $result = DB::select($this->rawQuery);

        return $this->serialize(collect($result));
    }

    /**
     * @param Collection $data
     * @return array
     */
    abstract public function serialize(Collection $data): array;
}
