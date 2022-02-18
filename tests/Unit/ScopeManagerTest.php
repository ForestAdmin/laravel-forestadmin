<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;


use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\ScopeManager;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
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
    public function testFetchScopes(): void
    {
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
