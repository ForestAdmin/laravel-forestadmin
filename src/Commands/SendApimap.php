<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use ForestAdmin\AgentPHP\Agent\Builder\AgentFactory;
use ForestAdmin\AgentPHP\Agent\Facades\Cache;
use ForestAdmin\LaravelForestAdmin\Providers\AgentProvider;
use Illuminate\Console\Command;

class SendApimap extends Command
{
    protected $signature = 'forest:send-apimap';

    protected $description = 'Send the apimap to Forest';

    public function handle()
    {
        Cache::flush();
        AgentProvider::getAgentInstance()->sendSchema();

        $this->info('✅ Apimap sent');
    }
}
