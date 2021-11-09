<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\InvalidUrlException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use RuntimeException;

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
     * @throws GuzzleException
     * @return void
     */
    public function testGetRequest(): void
    {
        $this->mockResponse();
        $response = $this->forestApi->get('/foo', [], $this->headers);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertTrue($response->hasHeader('foo'));
        $this->assertArrayHasKey('forest-secret-key', $this->forestApi->getHeaders());
        $this->assertSame($this->forestApi->getHeaders()['forest-secret-key'], 'my-secret-key');
    }

    /**
     * @throws GuzzleException
     * @return void
     */
    public function testPostRequest(): void
    {
        $this->mockResponse();
        $response = $this->forestApi->post('/foo', [], ['key' => 'value'], $this->headers);

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertTrue($response->hasHeader('foo'));
        $this->assertArrayHasKey('forest-secret-key', $this->forestApi->getHeaders());
        $this->assertSame($this->forestApi->getHeaders()['forest-secret-key'], 'my-secret-key');
    }

    /**
     * @throws GuzzleException
     * @return void
     */
    public function testGetExceptionRequest(): void
    {
        $this->mockResponseException();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now');

        $this->forestApi->get('/foo');
    }

    /**
     * @throws GuzzleException
     * @return void
     */
    public function testPostExceptionRequest(): void
    {
        $this->mockResponseException();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now');
        $this->forestApi->post('/foo');
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testValidUrlRequest(): void
    {
        $makeUrl = $this->invokeMethod($this->forestApi, 'validateUrl', [config('forest.api.url') . '/foo']);
        $this->assertTrue($makeUrl);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testInvalidUrlExceptionRequest(): void
    {
        app()['config']->set('app.debug', false);
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage(config('forest.api.url') . 'foo seems to be an invalid url');

        $this->invokeMethod($this->forestApi, 'makeUrl', ['foo']);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test error');

        $this->invokeMethod($this->forestApi, 'throwException', ['test error']);
    }

    /**
     * @return void
     */
    public function mockResponse(): void
    {
        $mock = new MockHandler([new Response(200, ['foo' => 'bar'], 'ok'),]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->forestApi->setClient($client);
    }

    /**
     * @return void
     */
    public function mockResponseException(): void
    {
        $mock = new MockHandler([new RuntimeException('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now'),]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->forestApi->setClient($client);
    }
}
