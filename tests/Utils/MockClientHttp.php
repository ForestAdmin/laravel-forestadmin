<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use Mockery as m;

/**
 * Class MockClientHttp
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait MockClientHttp
{
    /**
     * @param int    $status
     * @param string $body
     * @param int    $responseCallLimit
     * @param int    $clientCallLimit
     * @return m\LegacyMockInterface|m\MockInterface|string
     */
    public function mockClient(int $status = 200, string $body = '{"access_token":"mock_access_token"}', $responseCallLimit = 1, $clientCallLimit = 1)
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')->times($responseCallLimit)->andReturn('application/json');
        $response->shouldReceive('getBody')->andReturn($body);
        $response->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times($clientCallLimit)->andReturn($response);
        $this->provider->setHttpClient($client);

        return $client;
    }
}
