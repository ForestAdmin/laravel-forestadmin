<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\AgentPHP\Agent\Http\ForestController as BaseForestController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForestController extends BaseForestController
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        $request->attributes->set('_route', Route::currentRouteName());
        $request->attributes->set('_route_params', Route::current()->parameters());

        return parent::__invoke($request);
    }
}
