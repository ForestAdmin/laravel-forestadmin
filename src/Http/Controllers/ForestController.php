<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Permissions\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Class ForestController
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestController extends Controller
{
    use AuthorizesRequests {
        authorize as baseAuthorize;
    }

    /**
     * @param string $ability
     * @param        $arguments
     * @return mixed
     * @throws AuthorizationException
     */
    public function can(string $ability, $arguments)
    {
        if (! Permission::$ability(Auth::guard('forest')->user(), $arguments)) {
            throw new AuthorizationException();
        }
    }
}
