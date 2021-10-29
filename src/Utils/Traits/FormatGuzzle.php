<?php

namespace ForestAdmin\LaravelForestAdmin\Utils\Traits;

use GuzzleHttp\Psr7\Response;

/**
 * Class FormatGuzzle
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
trait FormatGuzzle
{
    /**
     * @param Response $response
     * @return mixed
     * @throws \JsonException
     */
    public function getBody(Response $response)
    {
        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
