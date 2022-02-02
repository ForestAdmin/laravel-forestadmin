<?php

namespace ForestAdmin\LaravelForestAdmin\Policies;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

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
     * Determine whether the user can view any models.
     *
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function viewAny(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'browseEnabled');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function view(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'readEnabled');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function create(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'addEnabled');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param ForestUser $forestUser
     * @param mixed      $collection
     * @return Response|bool
     */
    public function update(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'editEnabled');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param ForestUser $forestUser
     * @param            $collection
     * @return Response|bool
     */
    public function delete(ForestUser $forestUser, $collection)
    {
        return $forestUser->hasPermission($this->getCollectionName($collection), 'deleteEnabled');
    }

    /**
     * Determine whether the user can delete the model.
     *
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
     * @param mixed      $collection
     * @param string     $smartAction
     * @return Response|bool
     */
    public function smartAction(ForestUser $forestUser, $collection, string $smartAction)
    {
        return $forestUser->hasSmartActionPermission($this->getCollectionName($collection), $smartAction);
    }

    /**
     * @param mixed $collection
     * @return string
     */
    private function getCollectionName($collection): string
    {
        return strtolower(class_basename(get_class($collection)));
    }
}
