<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Exports\CollectionExport;
use ForestAdmin\LaravelForestAdmin\Listeners\RouteMatched;
use ForestAdmin\LaravelForestAdmin\Schema\Schema;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Category;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\MockForestUserFactory;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Prophecy\Argument;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResourcesControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class RouteMatchedTest extends TestCase
{
    use FakeData;
    use FakeSchema;
    use MockForestUserFactory;
    use ScopeManagerFactory;

    /**
     * @var ForestUser
     */
    private ForestUser $forestUser;

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('forest.models_namespace', 'ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\\');
        $app['config']->set('forest.send_apimap_auto', true);
    }

    /**
     * @return void
     */
    public function testApiMapNotSendWithOutFile(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(config('forest.json_file_path'));
        $schema = new Schema($this->getConfig(), new ForestApiRequester(), $this->getConsole('<info>Apimap Received<info>'));
        App::shouldReceive('make')->andReturn($schema);

        $this->assertNull(Cache::get(RouteMatched::APIMAP_DATE));
        $this->get('/forest');
        $this->assertNull(Cache::get(RouteMatched::APIMAP_DATE));
    }

    /**
     * @return void
     */
    public function testApiMapRouteNotMatchPattern(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(config('forest.json_file_path'));
        $schema = new Schema($this->getConfig(), new ForestApiRequester(), $this->getConsole('<info>Apimap Received<info>'));
        App::shouldReceive('make')->andReturn($schema);

        $this->assertNull(Cache::get(RouteMatched::APIMAP_DATE));
        $this->get('/foo');
        $this->assertNull(Cache::get(RouteMatched::APIMAP_DATE));
    }

    /**
     * @return void
     */
    public function testApiMapSend(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(config('forest.json_file_path'));
        $schema = new Schema($this->getConfig(), $this->forestApiPost(), $this->getConsole('<info>Apimap Received<info>'));
        App::shouldReceive('make')->andReturn($schema);
        file_put_contents(App::basePath(config('forest.json_file_path')), '{}');

        $this->assertNull(Cache::get(RouteMatched::APIMAP_DATE));
        $this->get('/forest');
        $this->assertNotNull(Cache::get(RouteMatched::APIMAP_DATE));
        $this->assertEquals(File::lastModified(config('forest.json_file_path')), Cache::get(RouteMatched::APIMAP_DATE));
        File::delete(config('forest.json_file_path'));
    }

    /**
     * @return void
     */
    public function testApiMapSendOnce(): void
    {
        App::partialMock()->shouldReceive('basePath')->andReturn(config('forest.json_file_path'));
        $schema = new Schema($this->getConfig(), new ForestApiRequester(), $this->getConsole('<info>Apimap Received<info>'));
        App::shouldReceive('make')->andReturn($schema);
        file_put_contents(App::basePath(config('forest.json_file_path')), '{}');

        Cache::put(RouteMatched::APIMAP_DATE, File::lastModified(config('forest.json_file_path')));
        $this->get('/forest');
        $this->assertEquals(File::lastModified(config('forest.json_file_path')), Cache::get(RouteMatched::APIMAP_DATE));
        File::delete(config('forest.json_file_path'));
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
                new \GuzzleHttp\Psr7\Response(204, [], null)
            );

        return $forestApiPost->reveal();
    }

    /**
     * @return object
     */
    private function getConfig()
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

    /**
     * @return object
     */
    public function getConsole()
    {
        $console = $this->prophesize(ConsoleOutput::class);

        return $console->reveal();
    }
}
