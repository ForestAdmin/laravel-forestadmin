<?php

namespace ForestAdmin\LaravelForestAdmin;

use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Class ForestServiceProvider
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestServiceProvider extends ServiceProvider
{
    /**
     * @var string $serveCommand
     */
    protected string $serveCommand = 'artisan serve';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        // publish configuration files
        $this->publishes(
            [
                $this->configFile() => $this->app['path.config'] . DIRECTORY_SEPARATOR . 'forest.php',
            ],
            'config'
        );

        if (null !== Request::server('argv')) {
            $currentCommand = implode(' ', Request::server('argv'));
            if (Str::startsWith($currentCommand, $this->serveCommand)) {
                $this->app['events']->listen(ArtisanStarting::class, [Schema::class, 'handle']);
            }
        }
    }

    /**
     * merge module config if it's not published or some entries are missing
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->configFile(), 'forest');
    }

    /**
     * Get path schema file.
     *
     * @return string
     */
    protected function configFile(): string
    {
        return realpath(__DIR__ . '/../config/forest.php');
    }
}
