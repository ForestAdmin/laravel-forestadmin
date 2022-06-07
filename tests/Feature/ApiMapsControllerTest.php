<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ApiMapsController;
use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class ApiMapsControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ApiMapsControllerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function testIndex(): void
    {
        $schema = new Schema(config(), $this->forestApiPost(), $this->getConsole('<info>Apimap Received<info>'));
        app()->instance(Schema::class, $schema);
        $apiMapsController = new ApiMapsController();
        $indexRoute = $apiMapsController->index();

        $this->assertEmpty($indexRoute->getContent());
        $this->assertEquals(204, $indexRoute->getStatusCode());
    }

    /**
     * @return void
     * @throws BindingResolutionException|\JsonException
     */
    public function testIndexWithoutForestSecret(): void
    {
        Config::set('forest.api.secret', null);
        $apiMapsController = new ApiMapsController();
        $indexRoute = $apiMapsController->index();
        $data = json_decode($indexRoute->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals('forest secret is missing', $data['error']);
        $this->assertEquals(401, $indexRoute->getStatusCode());
    }

    /**
     * @return object
     */
    public function forestApiPost()
    {
        $forestApiPost = $this->prophesize(ForestApiRequester::class);
        $forestApiPost
            ->post(Argument::type('string'), Argument::size(0), Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, [], null)
            );

        return $forestApiPost->reveal();
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
