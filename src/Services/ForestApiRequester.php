<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use ForestAdmin\LaravelForestAdmin\Exceptions\InvalidUrlException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Class ForestApiRequester
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestApiRequester
{
    /**
     * @var array
     */
    private array $headers;

    /**
     * ForestApiRequester constructor
     */
    public function __construct()
    {
        $this->headers = [
            'Content-Type'      => 'application/json',
            'forest-secret-key' => config('forest.api.secret'),
        ];
        $this->client = new Client();
    }

    /**
     * @param string $route
     * @param array  $query
     * @param array  $headers
     * @return Response
     * @throws GuzzleException
     */
    public function get(string $route, array $query = [], array $headers = []): Response
    {
        $url = $this->makeUrl($route);
        $params = $this->getParams($query, [], $this->headers($headers));

        return $this->call('get', $url, $params);
    }

    /**
     * @param string $route
     * @param array  $query
     * @param array  $body
     * @param array  $headers
     * @return Response
     * @throws GuzzleException
     */
    public function post(string $route, array $query = [], array $body = [], array $headers = []): Response
    {
        $url = $this->makeUrl($route);
        $params = $this->getParams($query, $body, $this->headers($headers));

        return $this->call('post', $url, $params);
    }

    /**
     * @param array $query
     * @param array $body
     * @param array $headers
     * @return array[]
     */
    public function getParams(array $query = [], array $body = [], array $headers = []): array
    {
        return [
            'headers' => $headers,
            'query'   => $query,
            'json'    => $body,
            'verify'  => !config('app.debug'),
        ];
    }

    /**
     * @param ClientInterface $client
     * @return void
     */
    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $params
     * @return Response
     * @throws GuzzleException
     */
    private function call(string $method, string $url, array $params = []): Response
    {
        try {
            $client = $this->client;
            $response = $client->request($method, $url, $params);
        } catch (\Exception $e) {
            $this->throwException("Cannot reach Forest API at $url, it seems to be down right now");
        }

        return $response;
    }

    /**
     * @param array $headers
     * @return array
     */
    private function headers(array $headers = []): array
    {
        $this->headers = array_merge(
            $this->headers,
            $headers
        );

        return $this->headers;
    }

    /**
     * @param string $route
     * @return string
     * @throws InvalidUrlException
     */
    private function makeUrl(string $route): string
    {
        if (!Str::of($route)->startsWith('https://')) {
            $route = config('forest.api.url') . $route;
        }

        if (!config('app.debug')) {
            $this->validateUrl($route);
        }

        return $route;
    }

    /**
     * Verify whether url is correct
     *
     * @param string $url
     * @return bool
     * @throws InvalidUrlException
     */
    private function validateUrl(string $url): bool
    {
        if ((bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) !== true) {
            throw new InvalidUrlException("$url seems to be an invalid url");
        }

        return true;
    }

    /**
     * @param $message
     * @return void
     */
    private function throwException($message): void
    {
        throw new RuntimeException($message);
    }
}
