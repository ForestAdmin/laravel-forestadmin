<?php

namespace ForestAdmin\LaravelForestAdmin\Schema;

use Composer\Autoload\ClassMapGenerator;
use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\SchemaException;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Utils\Database;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Schema
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class Schema
{
    use FormatGuzzle;

    public const LIANA_NAME = 'laravel-forestadmin';

    public const LIANA_VERSION = '0.0.1';

    /**
     * @var string
     */
    protected string $directory;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @param Config             $config
     * @param ForestApiRequester $forestApi
     */
    public function __construct(Config $config, ForestApiRequester $forestApi)
    {
        $this->config = $config;
        $this->directory = base_path($config->get('forest.models_directory'));
        $this->forestApi = $forestApi;
    }

    /**
     * @return void
     * @throws Exception
     * @throws GuzzleException
     * @throws BindingResolutionException
     */
    public function sendApiMap(): void
    {
        $response = $this->forestApi->post(
            '/forest/apimaps',
            [],
            $this->serialize()
        );

        $output = new ConsoleOutput();
        $output->write('🌳🌳🌳 ');

        if (in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_ACCEPTED, Response::HTTP_NO_CONTENT], true)) {
            $output->writeln('<info>Apimap Received<info>');
        } else {
            $output->writeln('<error>Cannot send the apimap to Forest. Are you online?</error>');
        }
    }

    /**
     * @return array
     * @throws Exception
     * @throws SchemaException
     * @throws BindingResolutionException
     */
    private function generate(): array
    {
        $files = $this->fetchFiles();
        $schema = new Collection($this->metadata());
        $collections = [];

        foreach ($files as $file) {
            if (class_exists($file)) {
                $class = (new \ReflectionClass($file));
                if ($class->isSubclassOf(Model::class) && $class->isInstantiable()) {
                    $model = app()->make($file);
                    $forestModel = new ForestModel($model);
                    $collections[] = $forestModel->serialize();
                }
            }
        }
        $schema->put('collections', $collections);

        try {
            File::put($this->config->get('forest.json_file_path'), json_encode($schema, JSON_PRETTY_PRINT));
        } catch (\RuntimeException $e) {
            throw new SchemaException("The schema cannot be saved in your application");
        }

        return $schema->toArray();
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     * @return array
     */
    private function serialize(): array
    {
        $schema = $this->generate();
        $data = [];
        $included = [];

        foreach ($schema['collections'] as $collection) {
            $data[] = [
                'id'            => $collection['name'],
                'type'          => 'collections',
                'attributes'    => $collection,
                'relationships' => [
                    'actions'  => [
                        'data' => []
                    ],
                    'segments' => [
                        'data' => []
                    ]
                ]
            ];
        }

        return [
            'data'     => $data,
            'included' => $included,
            'meta'     => $schema['meta'],
        ];
    }

    /**
     * Fetch all files in the model directory
     * @return Collection
     */
    private function fetchFiles(): Collection
    {
        $files = new Collection();

        foreach (glob($this->directory, GLOB_ONLYDIR) as $dir) {
            if (file_exists($dir)) {
                $fileClass = ClassMapGenerator::createMap($dir);
                foreach (array_keys($fileClass) as $file) {
                    $files->push($file);
                }
            }
        }

        return $files;
    }

    /**
     * @return array
     */
    private function metadata(): array
    {
        return [
            'meta' => [
                'liana'         => self::LIANA_NAME,
                'liana_version' => self::LIANA_VERSION,
                'stack'         => [
                    'database_type' => Database::getSource($this->config->get('database.default')),
                    'orm_version'   => app()->version(),
                ],
            ],
        ];
    }
}
