<?php

namespace ForestAdmin\LaravelForestAdmin\Serializer;

use Illuminate\Support\Str;
use League\Fractal\Serializer\JsonApiSerializer as FractalJsonApiSerializer;

/**
 * Class JsonApiSerializer
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 * @codeCoverageIgnore
 */
class JsonApiSerializer extends FractalJsonApiSerializer
{
    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        $id = $this->getIdFromData($data);

        $resource = [
            'data' => [
                'type' => $resourceKey,
                'id' => "$id",
                'attributes' => $data,
            ],
        ];

        unset($resource['data']['attributes']['id']);

        if (isset($resource['data']['attributes']['links'])) {
            unset($resource['data']['attributes']['links']);
        }

        if (isset($resource['data']['attributes']['meta'])) {
            $resource['data']['meta'] = $data['meta'];
            unset($resource['data']['attributes']['meta']);
        }

        if (empty($resource['data']['attributes'])) {
            $resource['data']['attributes'] = (object) [];
        }

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function injectAvailableIncludeData($data, $availableIncludes)
    {
        if (!$this->shouldIncludeLinks()) {
            return $data;
        }

        if ($this->isCollection($data)) {
            $data['data'] = array_map(function ($resource) use ($availableIncludes) {
                foreach ($availableIncludes as $relationshipKey) {
                    $resource = $this->addRelationshipLinks($resource, $relationshipKey);
                }
                return $resource;
            }, $data['data']);
        } else {
            foreach ($availableIncludes as $relationshipKey) {
                $data['data'] = $this->addRelationshipLinks($data['data'], $relationshipKey);
            }
        }

        return $data;
    }

    /**
     * Serialize the meta.
     *
     * @param array $meta
     *
     * @return array
     */
    public function meta(array $meta)
    {
        if (empty($meta)) {
            return [];
        }

        $result['meta'] = $meta;

        return $result;
    }

    /**
     * Adds links for all available includes to a single resource.
     *
     * @param array $resource         The resource to add relationship links to
     * @param string $relationshipKey The resource key of the relationship
     */
    protected function addRelationshipLinks($resource, $relationshipKey)
    {
        if (!isset($resource['relationships']) || !isset($resource['relationships'][$relationshipKey])) {
            $resource['relationships'][$relationshipKey] = [];
        }

        $type = Str::camel($resource['type']);

        $resource['relationships'][$relationshipKey] = array_merge(
            [
                'links' => [
                    'related' => [
                        'href' => "/forest/$type/{$resource['id']}/relationships/{$relationshipKey}",
                    ]
                ]
            ],
            $resource['relationships'][$relationshipKey]
        );

        return $resource;
    }
}
