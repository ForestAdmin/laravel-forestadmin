<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceGetter;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceCreator;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceRemover;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceUpdater;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
