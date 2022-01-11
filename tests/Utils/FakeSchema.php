<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

/**
 * Class FakeSchema
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait FakeSchema
{
    /**
     * @param bool $convertToJson
     * @return array|false|string
     * @throws \JsonException
     */
    public function fakeSchema(bool $convertToJson = true)
    {
        $schema = [
            "collections" => [
                [
                    "name"   => "book",
                    "fields" => [
                        [
                            "field"         => "id",
                            "type"          => "Number",
                            "default_value" => null,
                            "enums"         => null,
                            "integration"   => null,
                            "is_filterable" => true,
                            "is_read_only"  => false,
                            "is_required"   => false,
                            "is_sortable"   => true,
                            "is_virtual"    => false,
                            "is_searchable" => null,
                            "reference"     => null,
                            "inverse_of"    => null,
                            "widget"        => null,
                            "validations"   => [],
                        ],
                        [
                            "field"         => "label",
                            "type"          => "String",
                            "default_value" => null,
                            "enums"         => null,
                            "integration"   => null,
                            "is_filterable" => true,
                            "is_read_only"  => false,
                            "is_required"   => false,
                            "is_sortable"   => true,
                            "is_virtual"    => false,
                            "is_searchable" => null,
                            "reference"     => null,
                            "inverse_of"    => null,
                            "widget"        => null,
                            "validations"   => [],
                        ],
                        [
                            "field"         => "comments",
                            "type"          => ["Number"],
                            "default_value" => null,
                            "enums"         => null,
                            "integration"   => null,
                            "is_filterable" => true,
                            "is_read_only"  => false,
                            "is_required"   => false,
                            "is_sortable"   => true,
                            "is_virtual"    => false,
                            "is_searchable" => null,
                            "reference"     => "comment.id",
                            "inverse_of"    => "book",
                            "widget"        => null,
                            "validations"   => [],
                            "relationship"  => "HasMany",
                        ],
                    ],
                ],
            ],
        ];

        if ($convertToJson) {
            return json_encode($schema, JSON_THROW_ON_ERROR);
        } else {
            return $schema;
        }
    }
}
