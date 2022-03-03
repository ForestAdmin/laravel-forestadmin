<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Facades\JsonApi;
use ForestAdmin\LaravelForestAdmin\Http\Middleware\ForestAuthorization;
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
 * Class SmartActionController
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
abstract class AbstractSmartActionController extends ForestController
{
    /**
     * @var mixed $collection
     */
    protected mixed $collection;

    /**
     * SmartActionController construct
     *
     * @throws AuthorizationException
     */
    public function __construct()
    {
        $this->collection = app($this->getCollection());

        $this->middleware(ForestAuthorization::class);
    }

    /**
     * @return string
     */
    abstract public function getCollection(): string;

    /**
     * @param $method
     * @param $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AuthorizationException
     */
    public function callAction($method, $parameters)
    {
        // todo format method name
        $this->authorize('smartAction', $this->collection);

        return parent::callAction($method, $parameters);
    }
}
