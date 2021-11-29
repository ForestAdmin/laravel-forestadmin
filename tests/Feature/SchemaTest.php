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
use Mockery as m;
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
        App::shouldReceive('basePath')->andReturn(__DIR__ . '/../Feature/Models');
        $schema = new Schema($this->getConfig(), $this->forestApiPost(204));
        File::shouldReceive('put')->andReturn(true);

        $console = m::mock(ConsoleOutput::class);
        $console->shouldReceive('writeln')->andReturn('<info>Apimap Received<info>');

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
        App::shouldReceive('basePath')->andReturn(__DIR__ . '/../Feature/Models');
        $schema = new Schema($this->getConfig(), $this->forestApiPost(404));
        File::shouldReceive('put')->andReturn(true);

        $console = m::mock(ConsoleOutput::class);
        $console->shouldReceive('writeln')->andReturn('<error>Cannot send the apimap to Forest. Are you online?</error>');

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
            ->get('forest.models_directory')
            ->willReturn(__DIR__ . '/../Feature/Models');
        $config
            ->get('forest.json_file_path')
            ->willReturn('.forestadmin-schema.json');


        return $config->reveal();
    }
}
