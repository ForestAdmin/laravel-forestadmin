<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Middleware;

use Asm89\Stack\CorsService;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ForestCors
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestCors
{
    /**
     * @var CorsService $cors
     */
    protected CorsService $cors;

    /**
     * @var Container $container
     */
    protected Container $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->cors = new CorsService($this->getCorsOptions());
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next): Response
    {
        if (!$this->shouldRun($request)) {
            return $next($request);
        }

        if ($this->cors->isPreflightRequest($request)) {
            $response = $this->cors->handlePreflightRequest($request);
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');

            if ($request->headers->has('Access-Control-Request-Private-Network')) {
                $response->headers->set('Access-Control-Allow-Private-Network', 'true');
            }

            return $response;
        }

        $response = $next($request);

        if ($request->getMethod() === 'OPTIONS') {
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }

        return $this->addHeaders($request, $response);
    }

    /**
     * Add the headers to the Response, if they don't exist yet.
     *
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    protected function addHeaders(Request $request, Response $response): Response
    {
        if (!$response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if the request match with the config
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldRun(Request $request): bool
    {
        return Str::startsWith($request->getRequestUri(), '/forest');
    }


    /**
     * Get CORS
     *
     * @return array
     */
    protected function getCorsOptions(): array
    {
        return [
            'allowedHeaders'         => ['*'],
            'allowedMethods'         => ['GET', 'POST', 'PUT', 'DELETE'],
            'allowedOrigins'         => ['*.forestadmin.com'],
            'allowedOriginsPatterns' => ['#^.*\.forestadmin\.com\z#u'],
            'exposedHeaders'         => false,
            'maxAge'                 => 86400,
            'supportsCredentials'    => true,
        ];
    }
}
