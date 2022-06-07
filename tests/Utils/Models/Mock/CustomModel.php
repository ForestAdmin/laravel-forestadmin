<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Mock;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomModel
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class CustomModel extends Model
{
    /**
     * @return array
     */
    public function schemaFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function searchFields(): array
    {
        return ['id', 'foo'];
    }
}
