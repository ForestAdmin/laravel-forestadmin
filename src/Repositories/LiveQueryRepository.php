<?php

namespace ForestAdmin\LaravelForestAdmin\Repositories;

use ForestAdmin\LaravelForestAdmin\Repositories\Charts\LiveQuery\Concerns\RawQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class LiveQueryRepository
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class LiveQueryRepository
{
    use RawQuery;

    /**
     * @var string
     */
    protected string $type;

    /**
     * Construct LiveQuery
     */
    public function __construct()
    {
        $this->rawQuery = trim(request()->input('query'));
        $this->type = request()->input('type');
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $this->validateQuery();

        /*
         if @params['record_id']
            raw_query.gsub!('?', @params['record_id'].to_s)
         end
         */

        $result = DB::select($this->rawQuery);

        return $this->serialize(collect($result));
    }

    /**
     * @param Collection $data
     * @return array
     */
    abstract public function serialize(Collection $data): array;
}
