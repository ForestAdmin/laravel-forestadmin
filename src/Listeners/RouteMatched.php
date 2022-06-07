<?php

namespace ForestAdmin\LaravelForestAdmin\Listeners;

use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Events\RouteMatched as RouteMatchedEvent;
use Illuminate\Support\Str;

/**
 * Class RouteMatched
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RouteMatched
{
    public const APIMAP_DATE = 'forest:apimap-date';

    /**
     * @param RouteMatchedEvent $routeMatchedEvent
     * @return void
     */
    public function handle(RouteMatchedEvent $routeMatchedEvent): void
    {
        if ($this->shouldRun($routeMatchedEvent->request) && config('forest.send_apimap_automatic')) {
            $filePath = App::basePath(config('forest.json_file_path'));

            if (File::exists($filePath)) {
                $date = File::lastModified($filePath);
                if (Cache::get(self::APIMAP_DATE) !== $date) {
                    App::make(Schema::class)->sendApiMap();
                    Cache::put(self::APIMAP_DATE, $date);
                }
            }
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function shouldRun(Request $request): bool
    {
        return Str::startsWith($request->getRequestUri(), '/forest');
    }
}
