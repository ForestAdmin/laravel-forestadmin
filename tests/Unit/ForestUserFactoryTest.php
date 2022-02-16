<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\ForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class ForestUserFactoryTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestUserFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('forest.api.url', 'mock_host');
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testMakePermissionToUser(): void
    {
        $forestUser = new ForestUser(
            [
                'id'           => 1,
                'email'        => 'john.doe@forestadmin.com',
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'rendering_id' => 1,
                'tags'         => [],
                'team'         => 'Operations',
                'exp'          => 1643825269,
            ]
        );

        $factory = new ForestUserFactory($this->makeForestApi());
        $factory->makePermissionToUser($forestUser, 1);

        $this->assertEquals($this->getResponseFromApi()['stats'], $forestUser->getStats());
        $this->assertEquals(
            [
                'foo' => [
                    'browseEnabled',
                    'readEnabled',
                    'editEnabled',
                    'addEnabled',
                    'deleteEnabled',
                    'exportEnabled',
                ],
                'bar' => [
                    'browseEnabled',
                    'readEnabled',
                    'editEnabled',
                    'addEnabled',
                    'deleteEnabled',
                    'exportEnabled',
                ]
            ],
            $forestUser->getPermissions()->toArray()
        );
        $this->assertEquals(['foo' => ['demo']], $forestUser->getSmartActionPermissions()->toArray());
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testGetPermissions(): void
    {
        $factory = new ForestUserFactory($this->makeForestApi());
        $getPermissions = $this->invokeMethod($factory, 'getPermissions', [1, false]);

        $this->assertIsArray($getPermissions);
        $this->assertArrayHasKey('collections', $getPermissions);
        $this->assertArrayHasKey('renderings', $getPermissions);

        $formatResponse = $this->getResponseFromApi();
        $formatResponse['collections'] = $formatResponse['data']['collections'];
        $formatResponse['renderings'] = $formatResponse['data']['renderings'];
        unset($formatResponse['data']);

        $this->assertIsArray(Cache::get('permissions:rendering-1'));
        $this->assertEquals(Cache::get('permissions:rendering-1'), $formatResponse);
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testGetPermissionsForceFetch(): void
    {
        $factory = new ForestUserFactory($this->makeForestApi());
        $getPermissions = $this->invokeMethod($factory, 'getPermissions', [1, false]);

        $this->assertIsArray($getPermissions);
        $this->assertArrayHasKey('collections', $getPermissions);
        $this->assertArrayHasKey('renderings', $getPermissions);

        $formatResponse = $this->getResponseFromApi();
        $formatResponse['collections'] = $formatResponse['data']['collections'];
        $formatResponse['renderings'] = $formatResponse['data']['renderings'];
        unset($formatResponse['data']);

        $this->assertEquals(Cache::get('permissions:rendering-1'), $formatResponse);

        //--- same test but with force reloading permissions ---//
        $factory = new ForestUserFactory($this->makeForestApi(false));
        $getPermissions = $this->invokeMethod($factory, 'getPermissions', [1, true]);

        $this->assertIsArray($getPermissions);
        $this->assertArrayHasKey('collections', $getPermissions);
        $this->assertArrayHasKey('renderings', $getPermissions);

        $formatResponse = $this->getResponseFromApi(false);
        $formatResponse['collections'] = $formatResponse['data']['collections'];
        $formatResponse['renderings'] = $formatResponse['data']['renderings'];
        unset($formatResponse['data']);

        $this->assertEquals(Cache::get('permissions:rendering-1'), $formatResponse);
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testFetchPermissions(): void
    {
        $factory = new ForestUserFactory($this->makeForestApi());
        $fetchPermissions = $this->invokeMethod($factory, 'fetchPermissions', [1]);

        $this->assertIsArray($fetchPermissions);
        $this->assertEquals($fetchPermissions, $this->getResponseFromApi());
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFetchPermissionsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(ErrorMessages::UNEXPECTED);

        $factory = new ForestUserFactory($this->makeForestApiThrowException());
        $this->invokeMethod($factory, 'fetchPermissions', [1]);
    }

    /**
     * @param bool $allowed
     * @return object
     * @throws \JsonException
     */
    public function makeForestApi(bool $allowed = true)
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'), Argument::size(1))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], json_encode($this->getResponseFromApi($allowed), JSON_THROW_ON_ERROR))
            );

        return $forestApiGet->reveal();
    }

    /**
     * @return object
     */
    public function makeForestApiThrowException()
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'), Argument::size(1))
            ->shouldBeCalled()
            ->willThrow(new \RuntimeException());

        return $forestApiGet->reveal();
    }

    /**
     * @param bool $allowed
     * @return array
     */
    public function getResponseFromApi(bool $allowed = true): array
    {
        $permissions = $allowed ? [1] : [];
        return [
            'data'  => [
                'collections' => [
                    'foo' => [
                        'collection' => [
                            'browseEnabled' => $permissions,
                            'readEnabled'   => $permissions,
                            'editEnabled'   => $permissions,
                            'addEnabled'    => $permissions,
                            'deleteEnabled' => $permissions,
                            'exportEnabled' => $permissions,
                        ],
                        'actions'    => [
                            'demo' => [
                                'triggerEnabled' => true,
                            ],
                        ],
                    ],
                    'bar' => [
                        'collection' => [
                            'browseEnabled' => $permissions,
                            'readEnabled'   => $permissions,
                            'editEnabled'   => $permissions,
                            'addEnabled'    => $permissions,
                            'deleteEnabled' => $permissions,
                            'exportEnabled' => $permissions,
                        ],
                        'actions'    => [],
                    ],
                ],
                'renderings'  => [
                    1 => [
                        'foo' => [
                            'scope'    => null,
                            'segments' => [],
                        ],
                        'bar' => [
                            'scope'    => null,
                            'segments' => [],
                        ],
                    ],
                ],
            ],
            'stats' => [
                'queries'      => [],
                'leaderboards' => [],
                'lines'        => [],
                'objectives'   => [],
                'percentages'  => [],
                'pies'         => [],
                'values'       => [],
            ],
            'meta'  => [
                'rolesACLActivated' => true,
            ],
        ];
    }
}
