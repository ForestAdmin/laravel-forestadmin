<?php

namespace ForestAdmin\LaravelForestAdmin\Permissions;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Str;

/**
 * Class Permission
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class Permission
{
    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public static function viewAny(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission(self::getCollectionName($collection), 'browseEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public static function view(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission(self::getCollectionName($collection), 'readEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public static function create(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission(self::getCollectionName($collection), 'addEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public static function update(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission(self::getCollectionName($collection), 'editEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param            $collection
     * @return Response|bool
     */
    public static function delete(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission(self::getCollectionName($collection), 'deleteEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public static function export(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission(self::getCollectionName($collection), 'exportEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param array      $arguments
     * @return bool
     */
    public static function smartAction(ForestUser $forestUser, array $arguments = [])
    {
        [$collection, $action] = $arguments;
        return $forestUser->hasSmartActionPermission(self::getCollectionName($collection), $action);
    }

    /**
     * @param ForestUser $forestUser
     * @param            $query
     * @return bool
     */
    public static function liveQuery(ForestUser $forestUser, $query)
    {
        return $forestUser->hasLiveQueryPermission($query);
    }

    /**
     * @param ForestUser $forestUser
     * @param            $query
     * @return bool
     */
    public static function simpleCharts(ForestUser $forestUser, $query)
    {
        return $forestUser->hasSimpleChartPermission($query);
    }

    /**
     * @param mixed $collection
     * @return string
     */
    private static function getCollectionName($collection): string
    {
        return Str::camel((class_basename(get_class($collection))));
    }
}
