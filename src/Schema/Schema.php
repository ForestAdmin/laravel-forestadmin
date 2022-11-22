<?php

namespace ForestAdmin\LaravelForestAdmin\Schema;

use Composer\Autoload\ClassMapGenerator;
use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Services\ForestApiRequester;
use ForestAdmin\LaravelForestAdmin\Services\SmartFeatures\SmartCollection;
use ForestAdmin\LaravelForestAdmin\Utils\Database;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\FormatGuzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
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

    public const LIANA_VERSION = '1.2.2';

    /**
     * @var array
     */
    protected array $directories;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var ForestApiRequester
     */
    private ForestApiRequester $forestApi;

    /**
     * @var ConsoleOutput
     */
    private ConsoleOutput $console;

    /**
     * @param Config             $config
     * @param ForestApiRequester $forestApi
     * @param ConsoleOutput      $console
     */
    public function __construct(Config $config, ForestApiRequester $forestApi, ConsoleOutput $console)
    {
        $this->config = $config;
        $this->directories = is_string($config->get('forest.models_directory')) ? [$config->get('forest.models_directory')] : $config->get('forest.models_directory');
        $this->forestApi = $forestApi;
        $this->console = $console;
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

        $this->console->write('🌳🌳🌳 ');

        if (in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_ACCEPTED, Response::HTTP_NO_CONTENT], true)) {
            $this->console->writeln('<info>Apimap Received<info>');
        } else {
            $this->console->writeln('<error>Cannot send the apimap to Forest. Are you online?</error>');
        }
    }

    /**
     * @param string $modelName
     * @return bool
     */
    public function modelIncluded(string $modelName): bool
    {
        if (empty(config('forest.included_models')) && empty(config('forest.excluded_models'))) {
            return true;
        }

        if (!empty(config('forest.included_models'))) {
            return in_array($modelName, config('forest.included_models'), true);
        } else {
            return !in_array($modelName, config('forest.excluded_models'), true);
        }
    }

    /**
     * @return array
     * @throws BindingResolutionException
     * @throws Exception
     * @throws \JsonException
     */
    private function generate(): array
    {
        $schema = new Collection($this->metadata());
        $collections = [];
        foreach ($this->directories as $directory) {
            $path = App::basePath($directory);
            $files = $this->fetchFiles($path);

            foreach ($files as $file) {
                if (class_exists($file)) {
                    $class = (new \ReflectionClass($file));
                    if ($class->isSubclassOf(Model::class) && $class->isInstantiable() && $this->modelIncluded($file)) {
                        $model = app()->make($file);
                        $forestModel = new ForestModel($model);
                        $collections[$class->getName()] = $forestModel->serialize();
                    } elseif ($class->isSubclassOf(SmartCollection::class) && $class->isInstantiable()) {
                        $smartCollection = app()->make($file);
                        $collections[$class->getName()] = $smartCollection->serialize();
                    }
                }
            }
        }
        $schema->put('collections', array_values($collections));
        File::put($this->config->get('forest.json_file_path'), json_encode($schema, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return $schema->toArray();
    }

    /**
     * @return array
     * @throws BindingResolutionException
     * @throws Exception
     * @throws \JsonException
     */
    private function serialize(): array
    {
        $schema = $this->generate();
        $data = [];
        $included = [];

        foreach ($schema['collections'] as $collection) {
            $collectionActions = $collection['actions'];
            $collectionSegments = $collection['segments'];
            unset($collection['actions'], $collection['segments']);

            $included[] = $this->getSmartFeaturesByCollection('actions', $collectionActions, true);
            $included[] = $this->getSmartFeaturesByCollection('segments', $collectionSegments, true);

            $data[] = [
                'id'            => $collection['name'],
                'type'          => 'collections',
                'attributes'    => $collection,
                'relationships' => [
                    'actions'  => [
                        'data' => $this->getSmartFeaturesByCollection('actions', $collectionActions)
                    ],
                    'segments' => [
                        'data' => $this->getSmartFeaturesByCollection('segments', $collectionSegments)
                    ]
                ]
            ];
        }

        return [
            'data'     => $data,
            'included' => array_merge(...$included),
            'meta'     => $schema['meta'],
        ];
    }

    /**
     * @param string $type
     * @param array  $data
     * @param bool   $withAttributes
     * @return array
     */
    private function getSmartFeaturesByCollection(string $type, array $data, bool $withAttributes = false): array
    {
        $smartFeatures = [];

        foreach ($data as $value) {
            $smartFeature = [
                'id'   => $value['id'],
                'type' => $type,
            ];
            if ($withAttributes) {
                $smartFeature['attributes'] = $value;
            }
            $smartFeatures[] = $smartFeature;
        }

        return $smartFeatures;
    }


    /**
     * Fetch all files in the model directory
     *
     * @param string $directory
     * @return Collection
     */
    private function fetchFiles(string $directory): Collection
    {
        $files = new Collection();

        foreach (glob($directory, GLOB_ONLYDIR) as $dir) {
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
        $connection = $this->config->get('database.default');

        return [
            'meta' => [
                'liana'         => self::LIANA_NAME,
                'liana_version' => self::LIANA_VERSION,
                'stack'         => [
                    'database_type' => Database::getSource($this->config->get("database.connections.$connection.driver")),
                    'orm_version'   => app()->version(),
                ],
            ],
        ];
    }
}
