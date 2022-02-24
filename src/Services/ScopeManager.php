<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class ScopeManager
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ScopeManager
{
    use FormatGuzzle;

    public const TTL = 60 * 60 * 24;

    /**
     * @var ForestUser
     */
    private ForestUser $user;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @param ForestApiRequester $forestApi
     */
    public function __construct(ForestApiRequester $forestApi)
    {
        $this->user = Auth::guard('forest')->user();
        $this->forestApi = $forestApi;
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getScope(string $collection): array
    {
        return $this->getScopes()->get($collection) ?? [];
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'scope:rendering-' . $this->user->getAttribute('rendering_id');
    }

    /**
     * @return void
     */
    public function forgetCache(): void
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * @return Collection
     */
    protected function getScopes(): Collection
    {
        $renderingId = $this->user->getAttribute('rendering_id');
        $cacheKey = $this->getCacheKey();

        return Cache::remember(
            $cacheKey,
            self::TTL,
            function () use ($renderingId) {
                $fetchScopes = $this->fetchScopes($renderingId);
                return $this->formatConditions($fetchScopes);
            }
        );
    }

    /**
     * @param array $scopes
     * @return Collection
     */
    protected function formatConditions(array $scopes): Collection
    {
        $conditions = new Collection();
        foreach ($scopes as $collection => $scope) {
            $filters = [
                'filters' => [
                    'aggregator' => Arr::get($scope, 'scope.filter.aggregator'),
                    'conditions' => [],
                ],
            ];
            foreach (Arr::get($scope, 'scope.filter.conditions') as $condition) {
                if (Str::startsWith($condition['value'], '$currentUser')) {
                    $condition['value'] = Arr::get($scope, 'scope.dynamicScopesValues.users.' . $this->user->getKey())[$condition['value']];
                }
                $filters['filters']['conditions'][] = $condition;
            }
            $conditions->put($collection, $filters);
        }

        return $conditions;
    }

    /**
     * @param int $renderingId
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    protected function fetchScopes(int $renderingId): array
    {
        try {
            $response = $this->forestApi->get('/liana/scopes', compact('renderingId'));
        } catch (\RuntimeException $e) {
            throw new ForestApiException(ErrorMessages::UNEXPECTED);
        }

        return $this->getBody($response);
    }
}
