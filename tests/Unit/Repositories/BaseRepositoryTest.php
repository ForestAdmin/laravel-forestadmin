<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\BaseRepository;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Class ForestModelTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class BaseRepositoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testThrowException(): void
    {
        $model = $this->prophesize(Model::class);
        $baseRepository = m::mock(BaseRepository::class, [$model->reveal(), 'Foo'])
            ->makePartial();

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage('test error');

        $this->invokeMethod($baseRepository, 'throwException', ['test error']);
    }
}
