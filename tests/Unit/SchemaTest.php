<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Composer\Autoload\ClassMapGenerator;
use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class SchemaTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SchemaTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFetchFiles(): void
    {
        $schema = new Schema($this->getConfig(), $this->getForestApi());
        $this->invokeProperty($schema, 'directory', __DIR__ . '/../Feature/Models');
        $files = $this->invokeMethod($schema, 'fetchFiles');

        $directory = glob(__DIR__ . '/../Feature/Models', GLOB_ONLYDIR);
        $directoryFiles = new Collection();
        foreach ($directory as $dir) {
            if (file_exists($dir)) {
                $fileClass = ClassMapGenerator::createMap($dir);
                foreach (array_keys($fileClass) as $file) {
                    $directoryFiles->push($file);
                }
            }
        }

        $this->assertInstanceOf(Collection::class, $files);
        $this->assertEquals($files, $directoryFiles);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMetadata(): void
    {
        $schema = new Schema($this->getConfig(), $this->getForestApi());
        $metadata = $this->invokeMethod($schema, 'metadata');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('meta', $metadata);
        $this->assertArrayHasKey('liana', $metadata['meta']);
        $this->assertArrayHasKey('liana_version', $metadata['meta']);
        $this->assertArrayHasKey('stack', $metadata['meta']);
        $this->assertArrayHasKey('database_type', $metadata['meta']['stack']);
        $this->assertArrayHasKey('orm_version', $metadata['meta']['stack']);
        $this->assertEquals('laravel-forestadmin', $metadata['meta']['liana']);
        $this->assertEquals('foo_db', $metadata['meta']['stack']['database_type']);
    }

    /**
     * @return object
     */
    private function getForestApi()
    {
        return $this->prophesize(ForestApiRequester::class)->reveal();
    }

    /**
     * @return object
     */
    private function getConfig()
    {
        $config = $this->prophesize(Repository::class);
        $config
            ->get('database.default')
            ->willReturn('foo_db');
        $config
            ->get('forest.models_directory')
            ->willReturn(__DIR__ . '/../Feature/Models');

        return $config->reveal();
    }
}
