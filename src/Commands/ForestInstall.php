<?php

namespace ForestAdmin\LaravelForestAdmin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class ForestInstall
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class ForestInstall extends Command
{
    /**
     * @var string
     */
    protected $signature = 'forest:setup-keys {secret-key} {url}';

    /**
     * @var string
     */
    protected $description = 'Setup de keys on forest config file';

    /**
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');
        $appUrl = config('app.url');
        $documentationUrl = 'https://docs.forestadmin.com/documentation/how-tos/settings/laravel-specific-settings#onboard-with-laravel-valet';

        if (preg_match('/^(http:\/\/.*)(.test)$/', $url)) {
            $this->info("⚠️  If you use Valet, please activate SSL with valet secure command. See the <href=$documentationUrl>documentation</> for more information");
        }

        if ($url !== $appUrl) {
            $this->error("🌳🌳🌳 Something went wrong! The URL set on step 1 ($url) and you Laravel APP_URL ($appUrl) do not match. Please update as to make them match. 🌳🌳🌳");
            return;
        } else {
            $this->info('✅ Url properly configured');
        }

        if (Str::contains(file_get_contents($this->getEnvFilePath()), 'FOREST_AUTH_SECRET') === false) {
            $key = Str::random(32);
            file_put_contents($this->getEnvFilePath(), PHP_EOL . "FOREST_AUTH_SECRET=$key", FILE_APPEND);
            $this->info('✅ The forest auth key has been setup');
        } else {
            $this->warn('The forest auth key is already setup');
        }

        if (Str::contains(file_get_contents($this->getEnvFilePath()), 'FOREST_ENV_SECRET') === false) {
            $secretKey = $this->argument('secret-key');
            file_put_contents($this->getEnvFilePath(), PHP_EOL . "FOREST_ENV_SECRET=$secretKey" . PHP_EOL, FILE_APPEND);
            $this->info('✅ The forest secret key has been setup');
        } else {
            $this->warn('The forest secret key is already setup');
        }
    }

    /**
     * @return string
     */
    private function getEnvFilePath(): string
    {
        return app()->basePath('.env');
    }
}
