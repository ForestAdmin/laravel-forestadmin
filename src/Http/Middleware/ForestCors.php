<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Middleware;

use Asm89\Stack\CorsService;
use Closure;
use Illuminate\Http\Request;
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
     * @var CorsService
     */
    protected CorsService $corsService;

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->corsService = new CorsService($this->getCorsOptions());

        if ($this->corsService->isPreflightRequest($request)) {
            $response = $this->corsService->handlePreflightRequest($request);
            $this->corsService->varyHeader($response, 'Access-Control-Request-Method');

            return $response;
        }

        $response = $next($request);

        if ($request->getMethod() === 'OPTIONS') {
            $this->corsService->varyHeader($response, 'Access-Control-Request-Method');
        }

        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->corsService->addActualRequestHeaders($response, $request);
        }

        return $response;
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
