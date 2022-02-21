<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\ScopeManager;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class ScopeManagerTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class ScopeManagerTest extends TestCase
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
     * @throws \ReflectionException
     */
    public function testGetScopes(): void
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
        Auth::shouldReceive('guard->user')->andReturn($forestUser);
        $scopeManager = new ScopeManager($this->makeForestApi());
        $this->invokeProperty($scopeManager, 'user', $forestUser);
        $result = $this->invokeMethod($scopeManager, 'getScopes');
        $expected = collect(
            [
                'book' => [
                    'filters' => [
                        'aggregator' => 'and',
                        'conditions' => [
                            0 => [
                                'field'    => 'active',
                                'operator' => 'present',
                                'value'    => null,
                            ],
                            1 => [
                                'field'    => 'label',
                                'operator' => 'contains',
                                'value'    => 'John',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($expected, $result);
        $this->assertInstanceOf(Collection::class, Cache::get('scope:rendering-1'));
        $this->assertEquals(Cache::get('scope:rendering-1'), $expected);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFormatConditions(): void
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
        Auth::shouldReceive('guard->user')->andReturn($forestUser);
        $scopeManager = new ScopeManager(new ForestApiRequester());

        $this->invokeProperty($scopeManager, 'user', $forestUser);
        $result = $this->invokeMethod($scopeManager, 'formatConditions', [$this->getResponseFromApi()]);
        $expected = collect(
            [
                'book' => [
                    'filters' => [
                        'aggregator' => 'and',
                        'conditions' => [
                            0 => [
                                'field'    => 'active',
                                'operator' => 'present',
                                'value'    => null,
                            ],
                            1 => [
                                'field'    => 'label',
                                'operator' => 'contains',
                                'value'    => 'John',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testFetchScopes(): void
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
        Auth::shouldReceive('guard->user')->andReturn($forestUser);
        $scopeManager = new ScopeManager($this->makeForestApi());
        $fetchScopes = $this->invokeMethod($scopeManager, 'fetchScopes', [1]);

        $this->assertIsArray($fetchScopes);
        $this->assertEquals($fetchScopes, $this->getResponseFromApi());
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFetchPermissionsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(ErrorMessages::UNEXPECTED);
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
        Auth::shouldReceive('guard->user')->andReturn($forestUser);
        $scopeManager = new ScopeManager($this->makeForestApiThrowException());

        $this->invokeMethod($scopeManager, 'fetchScopes', [1]);
    }

    /**
     * @return object
     * @throws \JsonException
     */
    public function makeForestApi(): object
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'), Argument::size(1))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], json_encode($this->getResponseFromApi(), JSON_THROW_ON_ERROR))
            );

        return $forestApiGet->reveal();
    }

    /**
     * @return object
     */
    public function makeForestApiThrowException(): object
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'), Argument::size(1))
            ->shouldBeCalled()
            ->willThrow(new \RuntimeException());

        return $forestApiGet->reveal();
    }

    /**
     * @return array
     */
    public function getResponseFromApi(): array
    {
        return [
            'book' => [
                'scope' => [
                    'filter'              => [
                        'aggregator' => 'and',
                        'conditions' => [
                            [
                                'field'    => 'active',
                                'operator' => 'present',
                                'value'    => null,
                            ],
                            [
                                'field'    => 'label',
                                'operator' => 'contains',
                                'value'    => '$currentUser.firstName',
                            ],
                        ],
                    ],
                    'dynamicScopesValues' => [
                        'users' => [
                            '1' => [
                                '$currentUser.firstName' => 'John',
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }
}
