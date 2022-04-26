<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
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
        $ip1 = '100.2.3.10';
        $ip2 = '100.2.3.110';

        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $rule = $ipWhitelist->getRules()[1];

        $this->assertTrue($ipWhitelist->isIpMatchRule($ip1, $rule));
        $this->assertFalse($ipWhitelist->isIpMatchRule($ip2, $rule));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchRuleOnSubnet(): void
    {
        $ip1 = '180.10.10.10';
        $ip2 = '181.10.10.100';

        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $rule = $ipWhitelist->getRules()[2];

        $this->assertTrue($ipWhitelist->isIpMatchRule($ip1, $rule));
        $this->assertFalse($ipWhitelist->isIpMatchRule($ip2, $rule));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchRuleInvalidRule(): void
    {
        $ip = '127.0.0.1';
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $rule = [
            'type' => 4,
            'ip'   => $ip,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid rule type');

        $this->assertTrue($ipWhitelist->isIpMatchRule($ip, $rule));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchIp(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $this->assertTrue($ipWhitelist->isIpMatchIp('127.0.0.1', '127.0.0.1'));
        $this->assertFalse($ipWhitelist->isIpMatchIp('127.0.0.1', '192.168.0.1'));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchIpOnBothLoopback(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $this->assertTrue($ipWhitelist->isIpMatchIp('127.0.0.1', '::1'));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsSameIpVersion(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $this->assertTrue($ipWhitelist->isSameIpVersion('127.0.0.1', '127.0.0.1'));
        $this->assertFalse($ipWhitelist->isSameIpVersion('127.0.0.1', '::1'));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsBothLoopback(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $this->assertTrue($ipWhitelist->isBothLoopback('127.0.0.1', '::1'));
        $this->assertFalse($ipWhitelist->isBothLoopback('127.0.0.1', '::2'));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchRange(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $this->assertFalse($ipWhitelist->isIpMatchRange('10.0.0.1', '2002:a00:1::', '2002:a00:64::'));
        $this->assertTrue($ipWhitelist->isIpMatchRange('10.0.0.5', '10.0.0.1', '10.0.0.100'));
        $this->assertFalse($ipWhitelist->isIpMatchRange('10.0.0.110', '10.0.0.1', '10.0.0.100'));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testIsIpMatchSubnet(): void
    {
        $ipWhitelist = new IpWhitelist($this->makeForestApi());
        $this->assertTrue($ipWhitelist->isIpMatchSubnet('10.0.0.1', '10.0.0.0/24'));
        $this->assertFalse($ipWhitelist->isIpMatchSubnet('2002:a00:1::', '10.0.0.0/24'));
        $this->assertFalse($ipWhitelist->isIpMatchSubnet('11.0.0.1', '10.0.0.0/24'));
    }

    /**
     * @return void
     * @throws \JsonException
     * @throws \ReflectionException
     */
    public function testRetrieve(): void
    {
        $forestApiGet = $this->prophesize(ForestApiRequester::class);
        $forestApiGet
            ->get(Argument::type('string'))
            ->shouldBeCalled()
            ->willThrow(new \RuntimeException());

        $this->expectException(ForestApiException::class);
        $this->expectExceptionMessage(ErrorMessages::UNEXPECTED);

        new IpWhitelist($forestApiGet->reveal());
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
