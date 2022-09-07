<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Class ForestClear
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class ForestClear extends Command
{
    /**
     * @var string
     */
    protected $signature = 'forest:clear';

    /**
     * @var string
     */
    protected $description = 'Clear the ForestProvider data cache key';

    /**
     * @return int
     */
    public function handle()
    {
        Cache::forget(config('forest.api.secret') . '-client-data');
    }
}
