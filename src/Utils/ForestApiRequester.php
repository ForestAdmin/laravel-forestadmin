<?php

namespace ForestAdmin\LaravelForestAdmin\Utils;

use ForestAdmin\LaravelForestAdmin\Exceptions\InvalidUrlException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
     * @param string      $route
     * @param string|null $query
     * @param array       $headers
     *
     * @throws InvalidUrlException
     * @return Response
     */
    public function get(string $route, array $query = [], array $headers = []): Response
    {
        $url = $this->makeUrl($route);

        return $this->call('get', $url, $query, $headers);
    }

    /**
     * @param string $route
     * @param array  $data
     * @param array  $headers
     *
     * @throws InvalidUrlException
     * @return Response
     */
    public function post(string $route, array $data = [], array $headers = []): Response
    {
        $url = $this->makeUrl($route);

        return $this->call('post', $url, $data, $headers);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $data
     * @param array  $headers
     *
     * @return Response
     */
    private function call(string $method, string $url, array $data = [], array $headers = []): Response
    {
        $response = Http::withHeaders($this->getHeaders($headers))
            ->acceptJson()
            ->$method($url, $data);

        if (! $response->successful()) {
            throw new \RuntimeException("Cannot reach Forest API at $url, it seems to be down right now");
        }

        return $response;
    }

    /**
     * @param array $headers
     * @return array
     */
    private function getHeaders(array $headers = []): array
    {
        return array_merge(
            [
                'Content-Type'      => 'application/json',
                'forest_secret_key' => config('forest.api.secret'),
            ],
            $headers
        );
    }

    /**
     * @param string $route
     * @return string
     * @throws InvalidUrlException
     */
    private function makeUrl(string $route): string
    {
        $url = config('forest.api.url') . $route;
        $this->validateUrl($url);

        return $url;
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
            throw new InvalidUrlException("$url seems to be invalid url");
        }

        return true;
    }
}
