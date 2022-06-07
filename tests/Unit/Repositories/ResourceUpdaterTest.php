<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceUpdater;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class ResourceUpdatorTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceUpdaterTest extends TestCase
{
    use FakeSchema;
    use ScopeManagerFactory;

    /**
     * @return void
     * @throws \JsonException
     */
    public function setUp(): void
    {
        parent::setUp();

        $forestUser = new ForestUser(
            [
                'id'           => 1,
                'email'        => 'john.doe@forestadmin.com',
                'first_name'   => 'John',
                'last_name'    => 'Doe',
                'rendering_id' => 1,
                'tags'         => [],
                'teams'        => 'Operations',
                'exp'          => 1643825269,
            ]
        );
        //--- push instance of ScopeManager in App ---//
        $this->makeScopeManager($forestUser);
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $params = [
            'data'          => [
                'attributes' => [
                    'label'      => 'test label',
                    'comment'    => 'test comment',
                    'difficulty' => 'easy',
                    'amount'     => 22,
                    'active'     => true,
                    'options'    => ['key' => 'value'],
                    'other'      => 'N/A',
                ],
                'relationships' => [
                    'category' => [
                        'data' => [
                            'type' => 'categories',
                            'id'   => '1',
                        ],
                    ],
                ],
            ],
            'type'          => 'books',
        ];
        $request = Request::create('/', 'GET', $params);
        app()->instance('request', $request);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $repository = m::mock(ResourceUpdater::class, [Book::first(), 'Book'])
            ->makePartial();

        $data = $repository->update(1);
        $this->assertInstanceOf(Book::class, $data);
    }

    /**
     * @return void
     */
    public function testUpdateExeption(): void
    {
        $params = [
            'data'          => [
                'attributes' => [
                    'label'      => 'test label',
                    'comment'    => 'test comment',
                    'difficulty' => 'easy',
                    'amount'     => 22,
                    'active'     => true,
                    'options'    => ['key' => 'value'],
                    'other'      => 'N/A',
                ],
                'relationships' => [
                    'category' => [
                        'data' => [
                            'type' => 'categories',
                            'id'   => '100',
                        ],
                    ],
                ],
            ],
            'type'          => 'books',
        ];
        $request = Request::create('/', 'GET', $params);
        app()->instance('request', $request);
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));

        $repository = m::mock(ResourceUpdater::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);

        $repository->update(1);
    }
}
