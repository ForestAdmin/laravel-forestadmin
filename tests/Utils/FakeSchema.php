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
                    "name" => "book",
                    "fields" => [
                        [
                            "field" => "comments",
                            "type" => ["Number"],
                            "default_value" => null,
                            "enums" => null,
                            "integration" => null,
                            "is_filterable" => true,
                            "is_read_only" => false,
                            "is_required" => false,
                            "is_sortable" => true,
                            "is_virtual" => false,
                            "reference" => "comment.book_id",
                            "inverse_of" => "id",
                            "widget" => null,
                            "validations" => [],
                            "relationship" => "HasMany",
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
