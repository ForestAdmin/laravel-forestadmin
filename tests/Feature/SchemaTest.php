<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\ConsoleOutput;

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
     * @throws Exception
     * @throws GuzzleException
     * @throws BindingResolutionException
     * @return void
     */
    public function testHandle(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(__DIR__ . '/../Utils/Models');
        $schema = new Schema($this->getConfig(), $this->forestApiPost(204), $this->getConsole('<info>Apimap Received<info>'));
        File::shouldReceive('put')->andReturn(true);

        $schema->sendApiMap();
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     * @throws BindingResolutionException
     * @return void
     */
    public function testHandleException(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(__DIR__ . '/../Utils/Models');
        $schema = new Schema($this->getConfig(), $this->forestApiPost(404), $this->getConsole('<error>Cannot send the apimap to Forest. Are you online?</error>'));
        File::shouldReceive('put')->andReturn(true);

        $schema->sendApiMap();
    }

    /**
     * @param int $status
     * @return object
     */
    public function forestApiPost(int $status)
    {
        $forestApiPost = $this->prophesize(ForestApiRequester::class);
        $forestApiPost
            ->post(Argument::type('string'), Argument::size(0), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(
                new Response($status, [], null)
            );

        return $forestApiPost->reveal();
    }

    /**
     * @return object
     */
    public function getConfig()
    {
        $config = $this->prophesize(Repository::class);
        $config
            ->get('database.default')
            ->willReturn('sqlite');
        $config
            ->get('database.connections.sqlite.driver')
            ->willReturn('sqlite');
        $config
            ->get('forest.models_directory')
            ->willReturn([__DIR__ . '/../Feature/Models']);
        $config
            ->get('forest.json_file_path')
            ->willReturn('.forestadmin-schema.json');


        return $config->reveal();
    }

    /**
     * @param string $notice
     * @return object
     */
    public function getConsole(string $notice)
    {
        $console = $this->prophesize(ConsoleOutput::class);
        $console
            ->write('🌳🌳🌳 ')
            ->willReturn('🌳🌳🌳 ');

        $console
            ->writeln($notice)
            ->willReturn($notice);

        return $console->reveal();
    }
}
