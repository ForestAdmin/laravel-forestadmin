<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\InvalidUrlException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Class ForestApiRequesterTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestApiRequesterTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->forestApi = new ForestApiRequester();
        $this->headers = ['forest-secret-key' => 'my-secret-key'];
    }

    /**
     * @return void
     */
    public function testGetRequest(): void
    {
        Http::fake()
            ->accept('application/json')
            ->withHeaders($this->headers)
            ->get(
                config('forest.api.url') . '/foo',
                ['foo' => 'bar'],
            );

        $response = $this->forestApi->get('/foo', ['foo' => 'bar'], $this->headers);

        $this->assertTrue($response->ok());
        $this->assertArrayHasKey('forest-secret-key', $this->forestApi->getHeaders());
        $this->assertSame($this->forestApi->getHeaders()['forest-secret-key'], 'my-secret-key');
    }

    /**
     * @return void
     */
    public function testPostRequest(): void
    {
        Http::fake()
            ->accept('application/json')
            ->withHeaders($this->headers)
            ->post(
                config('forest.api.url') . '/foo',
                ['key' => 'value'],
            );
        $response = $this->forestApi->post('/foo', ['key' => 'value'], $this->headers);

        $this->assertTrue($response->ok());
        $this->assertArrayHasKey('forest-secret-key', $this->forestApi->getHeaders());
        $this->assertSame($this->forestApi->getHeaders()['forest-secret-key'], 'my-secret-key');
    }

    /**
     * @throws GuzzleException
     * @return void
     */
    public function testGetExceptionRequest(): void
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now');

        $mock = new MockHandler(
            [
                new ConnectionException('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now')
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $client->request('GET', config('forest.api.url') . '/foo');

        $this->forestApi->get('/foo', ['foo' => 'bar']);
    }


    /**
     * @throws GuzzleException
     * @return void
     */
    public function testPostExceptionRequest(): void
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now');

        $mock = new MockHandler(
            [
                new ConnectionException('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now')
            ]
        );
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $client->request('GET', config('forest.api.url') . '/foo');

        $this->forestApi->post('/foo', ['foo' => 'bar']);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testValidUrlRequest(): void
    {
        $makeUrl = $this->invokeMethod($this->forestApi, 'validateUrl', array(config('forest.api.url') . '/foo'));
        $this->assertTrue($makeUrl);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testInvalidUrlExceptionRequest(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage(config('forest.api.url') . 'foo seems to be an invalid url');

        $this->invokeMethod($this->forestApi, 'makeUrl', array('foo'));
    }
}
