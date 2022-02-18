<?php

namespace ForestAdmin\LaravelForestAdmin\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Services\ScopeManager;
use Illuminate\Http\Response;

/**
 * Class ScopesController
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ScopesController extends ForestController
{
    /**
     * @return Response
     */
    public function index()
    {
        app(ScopeManager::class)->forgetCache();

        return response()->noContent();
    }
}
