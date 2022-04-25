<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestApiException;
use ForestAdmin\LaravelForestAdmin\Utils\ErrorMessages;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use IPLib\Factory as IpAddress;
use IPLib\Range\Type as IpType;

/**
 * Class IpWhitelist
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class IpWhitelist
{
    use FormatGuzzle;

    public const RULE_MATCH_IP = 0;

    public const RULE_MATCH_RANGE = 1;

    public const RULE_MATCH_SUBNET = 2;

    /**
     * @var bool
     */
    private bool $useIpWhitelist = false;

    /**
     * @var array
     */
    private array $rules = [];

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @param ForestApiRequester $forestApi
     * @throws \JsonException
     */
    public function __construct(ForestApiRequester $forestApi)
    {
        $this->forestApi = $forestApi;
        $this->retrieve();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->useIpWhitelist && !empty($this->rules);
    }

    /**
     * @param string $ip
     * @return void
     * @throws \Exception
     */
    public function isIpMatchesAnyRule(string $ip)
    {
        foreach ($this->getRules() as $rule) {
            if ($this->isIpMatchRule($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $ip
     * @param array  $rule
     * @return bool
     * @throws \Exception
     */
    public function isIpMatchRule(string $ip, array $rule): bool
    {
        if ($rule['type'] === self::RULE_MATCH_IP) {
            return $this->isIpMatchIp($ip, $rule['ip']);
        } elseif ($rule['type'] === self::RULE_MATCH_RANGE) {
            return $this->isIpMatchRange($ip, $rule['ipMinimum'], $rule['ipMaximum']);
        } elseif ($rule['type'] === self::RULE_MATCH_SUBNET) {
            return $this->isIpMatchSubnet($ip, $rule['range']);
        } else {
            throw new \Exception('Invalid rule type');
        }
    }

    /**
     * @param $ip1
     * @param $ip2
     * @return bool
     */
    public function isIpMatchIp($ip1, $ip2): bool
    {
        if (!$this->isSameIpVersion($ip1, $ip2)) {
            return $this->isBothLoopback($ip1, $ip2);
        }

        if ($ip1 === $ip2) {
            return true;
        } else {
            return $this->isBothLoopback($ip1, $ip2);
        }
    }

    /**
     * @param string $ip1
     * @param string $ip2
     * @return bool
     */
    public function isSameIpVersion(string $ip1, string $ip2): bool
    {
        return IpAddress::parseAddressString($ip1)->getAddressType() === IpAddress::parseAddressString($ip2)->getAddressType();
    }

    /**
     * @param string $ip1
     * @param string $ip2
     * @return bool
     */
    public function isBothLoopback(string $ip1, string $ip2): bool
    {
        $rangeTypeIp1 = IpAddress::parseAddressString($ip1)->getRangeType();
        $rangeTypeIp2 = IpAddress::parseAddressString($ip2)->getRangeType();

        return IpType::T_LOOPBACK === $rangeTypeIp1 && IpType::T_LOOPBACK === $rangeTypeIp2;
    }

    /**
     * @param string $ip
     * @param string $min
     * @param string $max
     * @return bool
     */
    public function isIpMatchRange(string $ip, string $min, string $max): bool
    {
        if (!$this->isSameIpVersion($ip, $min)) {
            return false;
        }

        $ipMinimum = IpAddress::parseAddressString($min);
        $ipMaximum = IpAddress::parseAddressString($max);
        $ipValue = IpAddress::parseAddressString($ip);

        return $ipValue->getComparableString() >= $ipMinimum->getComparableString() && $ipValue->getComparableString() <= $ipMaximum->getComparableString();
    }

    /**
     * @param string $ip
     * @param string $subnet
     * @return bool
     */
    public function isIpMatchSubnet(string $ip, string $subnet): bool
    {
        $range = IpAddress::parseRangeString($subnet);
        $ipValue = IpAddress::parseAddressString($ip);

        if (!$this->isSameIpVersion($ip, $range->getStartAddress())) {
            return false;
        }

        return $ipValue->matches($range);
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws \JsonException
     */
    protected function retrieve(): void
    {
        try {
            $response = $this->forestApi->get('/liana/v1/ip-whitelist-rules');
        } catch (\RuntimeException $e) {
            throw new ForestApiException(ErrorMessages::UNEXPECTED);
        }

        $ipWhitelistData = $this->getBody($response)['data']['attributes'];

        $this->useIpWhitelist = $ipWhitelistData['use_ip_whitelist'];
        $this->rules = $ipWhitelistData['rules'];
    }
}
