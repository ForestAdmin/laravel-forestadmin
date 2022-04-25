<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class IpWhitelistTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class IpWhitelistTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsEnabledAndReturnTrue(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $enabled = $ipWhitelist->isEnabled();

        $this->assertTrue($enabled);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsEnabledAndReturnFalse(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi(false));
        $enabled = $ipWhitelist->isEnabled();

        $this->assertFalse($enabled);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchesAnyRuleAndReturnTrue(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $isIpMatchesAnyRule = $ipWhitelist->isIpMatchesAnyRule('127.0.0.1');

        $this->assertTrue($isIpMatchesAnyRule);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchesAnyRuleAndReturnFalse(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $isIpMatchesAnyRule = $ipWhitelist->isIpMatchesAnyRule('10.10.10.0');

        $this->assertFalse($isIpMatchesAnyRule);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchRuleOnMatchIp(): void
    {
        $ip1 = '127.0.0.1';
        $ip2 = '100.0.0.1';
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $rule = $ipWhitelist->getRules()[0];

        $this->assertTrue($ipWhitelist->isIpMatchRule($ip1, $rule));
        $this->assertFalse($ipWhitelist->isIpMatchRule($ip2, $rule));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchRuleOnRange(): void
    {
        $ip1 = '100.2.3.99';
        $ip2 = '100.2.3.200';
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $rule = $ipWhitelist->getRules()[1];
dd($rule, $ipWhitelist->isIpMatchRule($ip1, $rule));
        $this->assertTrue($ipWhitelist->isIpMatchRule($ip1, $rule));
        //$this->assertFalse($ipWhitelist->isIpMatchRule($ip2, $rule));
    }

    /**
     * @param bool $withRules
     * @return object
     * @throws \JsonException
     */
    public function makeForestApi(bool $withRules = true)
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
