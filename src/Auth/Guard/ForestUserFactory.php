<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\Guard;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class ForestUserFactory
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestUserFactory
{
    use FormatGuzzle;

    public const TTL = 60 * 60 * 24;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @param ForestApiRequester $forestApi
     */
    public function __construct(ForestApiRequester $forestApi)
    {
        $this->forestApi = $forestApi;
    }

    /**
     * @param ForestUser $forestUser
     * @param int        $renderingId
     * @param bool       $forceFetch
     * @return void
     */
    public function makePermissionToUser(ForestUser $forestUser, int $renderingId, bool $forceFetch = false): void
    {
        $permissions = $this->getPermissions($renderingId, $forceFetch);
        $forestUser->setStats($permissions['stats']);

        if (array_key_exists('collections', $permissions)) {
            $collections = array_keys($permissions['collections']);
            foreach ($collections as $collection) {
                foreach (['collection', 'actions'] as $type) {
                    if (array_key_exists($type, $permissions['collections'][$collection])) {
                        $actions = [];
                        foreach ($permissions['collections'][$collection][$type] as $key => $value) {
                            $value = $type === 'actions' ? $value['triggerEnabled'] : $value;
                            if ($value === true || (is_array($value) && in_array($forestUser->getKey(), $value, true))) {
                                $actions[] = $type === 'actions' ? Str::slug($key) : $key;
                            }
                        }
                        $method = $type === 'collection' ? 'addPermission' : 'addSmartActionPermission';
                        $forestUser->$method($collection, $actions);
                    }
                }
            }
        }
    }

    /**
     * @param int  $renderingId
     * @param bool $forceFetch
     * @return array
     */
    protected function getPermissions(int $renderingId, bool $forceFetch): array
    {
        $cacheKey = 'permissions:rendering-' . $renderingId;

        if ($forceFetch) {
            Cache::forget($cacheKey);
        }

        return Cache::remember(
            $cacheKey,
            self::TTL,
            function () use ($renderingId) {
                $permissions = $this->fetchPermissions($renderingId);

                if (array_key_exists('data', $permissions)) {
                    $permissions['collections'] = $permissions['data']['collections'];
                    $permissions['renderings'] = $permissions['data']['renderings'];
                    unset($permissions['data']);
                }

                return $permissions;
            }
        );
    }

    /**
     * @param int $renderingId
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    protected function fetchPermissions(int $renderingId): array
    {
        try {
            $response = $this->forestApi->get('/liana/v3/permissions', compact('renderingId'));
        } catch (\RuntimeException $e) {
            throw new ForestApiException(ErrorMessages::UNEXPECTED);
        }

        return $this->getBody($response);
    }
}
