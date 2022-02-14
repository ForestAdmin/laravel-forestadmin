<?php

namespace ForestAdmin\LaravelForestAdmin\Services\Concerns;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DatabaseHelper
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait DatabaseHelper
{
    /**
     * @var string
     */
    protected string $table;

    /**
     * @var string|null
     */
    protected ?string $database = null;

    /**
     * @param Model $model
     * @return array
     * @throws Exception
     */
    public function getColumns(Model $model): array
    {
        $connexion = $model->getConnection()->getDoctrineSchemaManager();
        $columns = $connexion->listTableColumns($model->getTable(), $this->database);

        return $columns;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string|null
     */
    public function getDatabase(): ?string
    {
        return $this->database;
    }
}
