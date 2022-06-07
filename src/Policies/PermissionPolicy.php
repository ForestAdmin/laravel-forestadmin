<?php

namespace ForestAdmin\LaravelForestAdmin\Policies;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Str;

/**
 * Class PermissionPolicy
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function viewAny(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'browseEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function view(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'readEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function create(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'addEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function update(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'editEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param            $collection
     * @return Response|bool
     */
    public function delete(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'deleteEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function export(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'exportEnabled');
    }

    /**
     * @param ForestUser $forestUser
     * @param            $collection
     * @param            $action
     * @return bool
     */
    public function smartAction(ForestUser $forestUser, $collection, $action)
    {
        return $forestUser->hasSmartActionPermission($this->getCollectionName($collection), $action);
    }

    /**
     * @param mixed $collection
     * @return string
     */
    private function getCollectionName($collection): string
    {
        return Str::camel((class_basename(get_class($collection))));
    }
}
