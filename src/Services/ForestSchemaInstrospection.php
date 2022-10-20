<?php

namespace ForestAdmin\LaravelForestAdmin\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonPath\InvalidJsonException;
use JsonPath\JsonObject;

/**
 * Class ForestSchemaInstrospection
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestSchemaInstrospection
{
    private JsonObject $schema;

    /**
     * @throws InvalidJsonException
     */
    public function __construct()
    {
        $filePath = App::basePath(config('forest.json_file_path'));
        $file = File::get($filePath);
        $this->schema = new JsonObject($file);
    }

    /**
     * @return JsonObject
     */
    public function getSchema(): JsonObject
    {
        return $this->schema;
    }

    /**
     * @param string $collection
     * @return string
     */
    public function getClass(string $collection): string
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].class");

        return $data ? $data[0] : '';
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getFields(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].fields");

        return $data ? $data[0] : [];
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getSmartFields(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].fields[?(@.is_virtual == true and @.reference == null)]");

        return $data ? collect($data)->mapWithKeys(fn($item) => [$item['field'] => $item])->all() : [];
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getSmartActions(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].actions[*]");

        return $data ?: [];
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getSmartSegments(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].segments[*]");

        return $data ?: [];
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getSmartRelationships(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].fields[?(@.is_virtual == true and @.reference != null)]");

        return $data ? collect($data)->mapWithKeys(fn($item) => [$item['field'] => $item])->all() : [];
    }

    /**
     * @param string $collection
     * @param string $field
     * @return string|null
     */
    public function getTypeByField(string $collection, string $field): ?string
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].fields[?(@.field == '$field')].type");

        return $data ? $data[0] : null;
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getRelatedData(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].fields[?(@.relationship == 'HasMany' or @.relationship == 'BelongsToMany')].field");
        $smartRelationships = $this->getSmartRelationships($collection);
        foreach ($smartRelationships as $relationship) {
            if (is_array($relationship['type'])) {
                $data[] = $relationship['field'];
            }
        }

        return $data ?: [];
    }

    /**
     * @param string $collection
     * @return array
     */
    public function getSingleRelationships(string $collection): array
    {
        $collection = Str::camel($collection);
        $data = $this->getSchema()->get("$..collections[?(@.name == '$collection')].fields[?(@.relationship == 'HasOne' or @.relationship == 'BelongsTo')].field");
        $smartRelationships = $this->getSmartRelationships($collection);
        foreach ($smartRelationships as $relationship) {
            if (! is_array($relationship['type'])) {
                $data[] = $relationship['field'];
            }
        }

        return $data ?: [];
    }
}
