<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\ScopeManager;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class ChartsControllerTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class ScopesControllerTest extends TestCase
{
    use ProphecyTrait;
    use MockForestUserFactory;

    /**
     * @var ForestUser
     */
    private ForestUser $forestUser;

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('forest.models_namespace', 'ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\\');
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->forestUser = new ForestUser(
            [
                'id'           => 1,
                'email'        => 'john.doe@forestadmin.com',
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'rendering_id' => 1,
                'tags'         => [],
                'teams'        => 'Operations',
                'exp'          => 1643825269,
            ]
        );

        $forestResourceOwner = new ForestResourceOwner(
            array_merge(
                [
                    'type'                              => 'users',
                    'two_factor_authentication_enabled' => false,
                    'two_factor_authentication_active'  => false,
                ],
                $this->forestUser->getAttributes()
            ),
            $this->forestUser->getAttribute('rendering_id')
        );

        $this->withHeader('Authorization', 'Bearer ' . $forestResourceOwner->makeJwt());
        $this->mockForestUserFactory();
    }


    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testIndex(): void
    {
        Auth::shouldReceive(
            [
                'guard->check' => true,
                'guard->user'  => $this->forestUser
            ]
        );

        $scopeManager = new ScopeManager($this->makeForestApi());

        //--- first put some data into the cache ---//
        $this->invokeMethod($scopeManager, 'getScopes');
        $expected = collect(
            [
                'book' => [
                    'filters' => [
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
                                'value'    => 'John',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertInstanceOf(Collection::class, Cache::get('scope:rendering-1'));
        $this->assertEquals(Cache::get($scopeManager->getCacheKey()), $expected);

        $call = $this->postJson('/forest/scope-cache-invalidation');

        $this->assertEquals(\Illuminate\Http\Response::HTTP_NO_CONTENT, $call->getStatusCode());
        $this->assertEmpty($call->getContent());
        $this->assertEmpty(Cache::get($scopeManager->getCacheKey()));
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
     * @return array
     */
    public function getResponseFromApi(): array
    {
        return [
            'book' => [
                'scope' => [
                    'filter'             => [
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
            ],
        ];
    }

    /**
     * @return Collection
     */
    public function getScopes(): Collection
    {
        return collect(
            [
                'book' => [
                    'filters' => [
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
                                'value'    => 'John',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
