<?php

namespace ForestAdmin\LaravelForestAdmin\Listeners;

use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class ArtisanStart
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class ArtisanStart
{
    /**
     * @param CommandStarting $event
     * @throws BindingResolutionException
     * @return void
     */
    public function handle(CommandStarting $event): void
    {
        if ($event->command === 'serve') {
            app()->make(Schema::class)->sendApiMap();
        }
    }
}
