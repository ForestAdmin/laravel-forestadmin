<?php

namespace ForestAdmin\LaravelForestAdmin\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Class RouteServiceProvider
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
    }
}
