<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use Illuminate\Console\Command;

class SendApimap extends Command
{
    protected $signature = 'forest:send-apimap';

    protected $description = 'Send the apimap to Forest';

    public function handle()
    {
        app()->make(AgentFactory::class)->sendSchema();

        $this->info('✅ Apimap sent');
    }
}
