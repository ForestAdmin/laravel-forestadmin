<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\ForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\ScopeManager;
use Illuminate\Support\Facades\Auth;
use Mockery as m;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Trait ScopeMangerFactory
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
trait ScopeManagerFactory
{
    use ProphecyTrait;

    /**
     * @param ForestUser $forestUser
     * @param array      $scopes
     * @return void
     * @throws \JsonException
     */
    public function makeScopeManager(ForestUser $forestUser, array $scopes = []): void
    {
        Auth::partialMock()->shouldReceive(
            [
                'guard->check' => true,
                'guard->user'  => $forestUser,
            ]
        );

        $scopeManager = new ScopeManager($this->makeForestApi($scopes));
        app()->instance(ScopeManager::class, $scopeManager);
    }

    /**
     * @param array $scopes
     * @return object
     * @throws \JsonException
     */
    public function makeForestApi(array $scopes = []): object
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'), Argument::size(1))
            ->willReturn(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode($scopes, JSON_THROW_ON_ERROR))
            );

        return $forestApiGet->reveal();
    }
}
