<?php

namespace ForestAdmin\LaravelForestAdmin\Facades;

use ForestAdmin\LaravelForestAdmin\Services\ChartApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;

/**
 * Class ChartApi
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 *
 * @method static JsonResponse renderValue(int $value)
 * @method static JsonResponse renderPie(array $data);
 * @method static JsonResponse renderLine(array $data);
 * @method static JsonResponse renderObjective(array $data);
 * @method static JsonResponse renderLeaderboard(array $data);
 *
 * @see ChartApiResponse
 */
class ChartApi extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'chart-api';
    }
}
