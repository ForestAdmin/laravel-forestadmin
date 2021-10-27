<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use ForestAdmin\LaravelForestAdmin\Exceptions\InvalidUrlException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
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
     * @return void
     */
    public function testGetExceptionRequest(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now');

        Http::fake(
            [
                config('forest.api.url') . '/foo' => Http::response(['foo' => 'bar'], 404),
            ]
        )
            ->accept('application/json')
            ->withHeaders($this->headers);

        $this->forestApi->get('/foo', ['foo' => 'bar']);
    }


    /**
     * @return void
     */
    public function testPostExceptionRequest(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot reach Forest API at ' . config('forest.api.url') . '/foo, it seems to be down right now');

        Http::fake(
            [
                config('forest.api.url') . '/foo' => Http::response(['foo' => 'bar'], 404),
            ]
        )
            ->accept('application/json')
            ->withHeaders($this->headers);

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
