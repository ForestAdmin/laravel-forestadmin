<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class SendApimap
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class SendApimap extends Command
{
    /**
     * @var string
     */
    protected $signature = 'forest:send-apimap';

    /**
     * @var string
     */
    protected $description = 'Send the apimap to Forest';

    /**
     * @return int
     * @throws BindingResolutionException
     */
    public function handle()
    {
        app()->make(Schema::class)->sendApiMap();
    }
}
