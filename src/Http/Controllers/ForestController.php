<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

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
     * @param $ability
     * @param $arguments
     * @return void
     * @throws AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        if (Auth::guard('forest')->check()) {
            Auth::shouldUse('forest');
        }

        $this->baseAuthorize($ability, $arguments);
    }
}
