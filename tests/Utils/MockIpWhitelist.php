<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Prophecy\Argument;

/**
 * Trait MockIpWhitelist
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
trait MockIpWhitelist
{
    /**
     * @param bool $withRules
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function mockIpWhitelist(bool $withRules = false): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApiIpWhiteList($withRules));

        app()->instance(IpWhitelist::class, $ipWhitelist);
    }

    /**
     * @param bool $withRules
     * @return object
     * @throws \JsonException
     */
    public function makeForestApiIpWhiteList(bool $withRules = true)
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], json_encode($this->getResponseFromApi($withRules), JSON_THROW_ON_ERROR))
            );

        return $forestApiGet->reveal();
    }

    /**
     * @param bool $withRules
     * @return array
     */
    public function getResponseFromApi(bool $withRules = true): array
    {
        $rules = [
            [
                'type' => 0,
                'ip'   => '127.0.0.1',
            ],
            [
                'type'      => 1,
                'ipMinimum' => '100.2.3.1',
                'ipMaximum' => '100.2.3.100',
            ],
            [
                'type'  => 2,
                'range' => '180.10.10.0/24',
            ],
        ];

        return [
            'data' => [
                'type'       => 'ip-whitelist-rules',
                'id'         => '1',
                'attributes' => [
                    'rules'            => $withRules ? $rules : [],
                    'use_ip_whitelist' => $withRules,
                ],
            ],
        ];
    }
}
