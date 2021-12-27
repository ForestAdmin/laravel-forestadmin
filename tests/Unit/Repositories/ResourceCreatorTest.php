<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\ResourceCreator;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use Illuminate\Http\Request;
use Mockery as m;

/**
 * Class ResourceCreatorTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ResourceCreatorTest extends TestCase
{
    use FakeData;

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $params = [
            'data'          => [
                'attributes' => [
                    'label'      => 'test label',
                    'comment'    => 'test comment',
                    'difficulty' => 'easy',
                    'amount'     => 10,
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
        $repository = m::mock(ResourceCreator::class, [Book::first(), 'Book'])
            ->makePartial();

        $data = $repository->create();
        $this->assertInstanceOf(Book::class, $data);
    }

    /**
     * @return void
     */
    public function testCreateExeption(): void
    {
        $params = [
            'data'          => [
                'attributes' => [
                    'label'      => 'test label',
                    'comment'    => 'test comment',
                    'difficulty' => 'easy',
                    'amount'     => 10,
                    'active'     => true,
                    'options'    => ['key' => 'value'],
                    'other'      => 'N/A',
                ],
            ],
            'type'          => 'books',
        ];
        $request = Request::create('/', 'GET', $params);
        app()->instance('request', $request);

        $this->getBook()->save();
        $repository = m::mock(ResourceCreator::class, [Book::first(), 'Book'])
            ->makePartial();

        $this->expectException(ForestException::class);

        $repository->create();
    }
}
