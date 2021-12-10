<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ApiMapsController;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class ApiMapsControllerTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ApiMapsControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndex(): void
    {
        $apiMapsController = new ApiMapsController();
        $indexRoute = $apiMapsController->index();

        $this->assertEmpty($indexRoute->getContent());
        $this->assertEquals(204, $indexRoute->getStatusCode());
    }
}
