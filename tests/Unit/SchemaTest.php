<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Composer\Autoload\ClassMapGenerator;
use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class ArtisanTest
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
        $schema = new Schema(config(), $this->getForestApi(), $this->getConsole());
        $files = $this->invokeMethod($schema, 'fetchFiles', [__DIR__ . '/../Utils/Models']);

        $directory = glob(__DIR__ . '/../Utils/Models', GLOB_ONLYDIR);
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
        $schema = new Schema(config(), $this->getForestApi(), $this->getConsole());
        $metadata = $this->invokeMethod($schema, 'metadata');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('meta', $metadata);
        $this->assertArrayHasKey('liana', $metadata['meta']);
        $this->assertArrayHasKey('liana_version', $metadata['meta']);
        $this->assertArrayHasKey('stack', $metadata['meta']);
        $this->assertArrayHasKey('database_type', $metadata['meta']['stack']);
        $this->assertArrayHasKey('orm_version', $metadata['meta']['stack']);
        $this->assertEquals('laravel-forestadmin', $metadata['meta']['liana']);
        $this->assertEquals('sqlite', $metadata['meta']['stack']['database_type']);
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGenerate(): void
    {
        $schema = new Schema(config(), $this->getForestApi(), $this->getConsole());
        File::shouldReceive('put')->andReturn(true);
        $generate = $this->invokeMethod($schema, 'generate');

        $this->assertIsArray($generate);
        $this->assertArrayHasKey('meta', $generate);
        $this->assertArrayHasKey('collections', $generate);
    }

    /**
     * @return void
     */
    public function testModelIncludedWithoutIncludedOrExcludedModels(): void
    {
        $schema = new Schema(config(), $this->getForestApi(), $this->getConsole());
        $this->assertTrue($schema->modelIncluded('foo'));
    }

    /**
     * @return void
     */
    public function testModelIncludedWithIncludedModelsConfig(): void
    {
        config()->set('forest.included_models', ['foo', 'bar']);
        $schema = new Schema(config(), $this->getForestApi(), $this->getConsole());
        $this->assertTrue($schema->modelIncluded('foo'));
        $this->assertFalse($schema->modelIncluded('foo2'));
    }

    /**
     * @return void
     */
    public function testModelIncludedWithExcludedModelsConfig(): void
    {
        config()->set('forest.excluded_models', ['foo', 'bar']);
        $schema = new Schema(config(), $this->getForestApi(), $this->getConsole());
        $this->assertFalse($schema->modelIncluded('foo'));
        $this->assertTrue($schema->modelIncluded('foo2'));
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
    public function getConsole()
    {
        $console = $this->prophesize(ConsoleOutput::class);

        return $console->reveal();
    }
}
