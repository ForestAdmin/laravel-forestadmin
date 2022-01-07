<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceUpdater;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Http\Request;
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
    use FakeData;

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

        $this->getBook()->save();
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

        $this->getBook()->save();
        $repository = m::mock(ResourceUpdater::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);

        $repository->update(1);
    }
}
