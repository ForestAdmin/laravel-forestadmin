<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Concerns\ChartHelper;
use ForestAdmin\LaravelForestAdmin\Transformers\ChartTransformer;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;

/**
 * Class ChartApiResponse
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ChartApiResponse
{
    use ChartHelper;

    /**
     * @param int $value
     * @return JsonResponse
     */
    public function renderValue(int $value): JsonResponse
    {
        return $this->toJson(
            [
                'countCurrent'  => $value,
            ]
        );
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function renderPie(array $data): JsonResponse
    {
        foreach ($data as $item) {
            $this->abortIf((!array_key_exists('key', $item) || !array_key_exists('value', $item)), $item, "'key', 'value'");
        }

        return $this->toJson($data);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function renderLine(array $data): JsonResponse
    {
        foreach ($data as $item) {
            $this->abortIf((!array_key_exists('label', $item) || !array_key_exists('values', $item)), $item, "'label', 'values'");
        }

        return $this->toJson($data);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function renderObjective(array $data): JsonResponse
    {
        $this->abortIf((!array_key_exists('objective', $data) || !array_key_exists('value', $data)), $data, "'objective', 'value'");

        return $this->toJson($data);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function renderLeaderboard(array $data): JsonResponse
    {
        foreach ($data as $item) {
            $this->abortIf((!array_key_exists('key', $item) || !array_key_exists('value', $item)), $item, "'key', 'value'");
        }

        return $this->toJson($data);
    }

    /**
     * @param $value
     * @return JsonResponse
     */
    public function toJson($value): JsonResponse
    {
        return response()->json(
            JsonApi::renderItem(
                [
                    'id'    => Uuid::uuid4(),
                    'value' => $value,
                ],
                'stats',
                ChartTransformer::class
            )
        );
    }
}
