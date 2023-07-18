<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ForestInstall extends Command
{
    protected $signature = 'forest:install {secretKey?} {envFileName=.env}';

    protected $description = 'Install the Forest admin : setup environment keys & publish the default Forest Admin configuration to the application';

    public function handle(): void
    {
        File::deleteDirectory(storage_path('framework/cache/data/forest'));

        if (null !== $this->argument('secretKey')) {
            $this->createNewKeysToEnvFile($this->argument('envFileName'));
        }
    }

    private function createNewKeysToEnvFile(string $envFileName): void
    {
        $keys = [
            'FOREST_AUTH_SECRET' => Str::random(32),
            'FOREST_ENV_SECRET'  => $this->argument('secretKey'),
        ];

        foreach ($keys as $key => $value) {
            file_put_contents(base_path() . '/' . $envFileName, PHP_EOL . "$key=$value", FILE_APPEND);
        }
        $this->info('<info>✅ Env keys correctly set</info>');
    }
}
