<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use ForestAdmin\LaravelForestAdmin\Services\IpWhitelist;
use Mockery as m;

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
     * @param bool  $enabled
     * @param array $rules
     * @return void
     */
    public function mockIpWhitelist(bool $enabled = false, array $rules = []): void
    {
        $ipWhitelist = m::mock(IpWhitelist::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $ipWhitelist->shouldReceive('retrieve')
            ->andReturn($rules);
        $ipWhitelist->shouldReceive('isEnabled')
            ->andReturn($enabled);

        app()->instance(IpWhitelist::class, $ipWhitelist);
    }
}
