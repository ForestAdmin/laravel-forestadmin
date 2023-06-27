<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ForestInstall extends Command
{
    protected $signature = 'forest:install {secretKey} {envFileName=.env}';

    protected $description = 'Install the Forest admin : setup environment keys & publish the default Forest Admin configuration to the application';

    public function handle(): void
    {
        File::deleteDirectory(storage_path('framework/cache/data/forest'));

        $keys = [
            'FOREST_AUTH_SECRET' => Str::random(32),
            'FOREST_ENV_SECRET'  => $this->argument('secretKey'),
        ];

        $this->addKeysToEnvFile($keys, $this->argument('envFileName'));

        $this->publishConfig();
    }

    private function addKeysToEnvFile(array $keys, string $envFileName): void
    {
        foreach ($keys as $key => $value) {
            file_put_contents(base_path() . '/' . $envFileName, PHP_EOL . "$key=$value", FILE_APPEND);
        }
        $this->info('<info>✅ Env keys correctly set</info>');
    }

    private function publishConfig(): void
    {
        $defaultConfigFile = __DIR__ . '/../../default.config';
        $publishFileName = base_path() . '/forest/symfony_forest_admin.php';
        if (! file_exists($publishFileName)) {
            $forestDirectory = base_path() . '/forest';
            if (! mkdir($forestDirectory) && ! is_dir($forestDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $forestDirectory));
            }
            copy($defaultConfigFile, $publishFileName);
            $this->info('<info>✅ Config file set</info>');
        } else {
            $this->info('<info>⚠️ Forest Admin config file already setup</info>');
        }
    }
}
