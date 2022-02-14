<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories\Charts;

use Doctrine\DBAL\Exception;
use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Repositories\Charts\Simple\Leaderboard;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class LeaderboardTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class LeaderboardTest extends TestCase
{
    use FakeData;
    use FakeSchema;

    

    /**
     * @return void
     */
    public function testSerialize(): void
    {
        $repository = m::mock(Leaderboard::class, [new Book()])
            ->makePartial();
        $data = ['foo' => 10, 'bar' => 20];
        $serialize = $repository->serialize($data);

        $this->assertIsArray($serialize);
        $this->assertEquals([['key' => 'foo', 'value' => 10], ['key' => 'bar', 'value' => 20]], $serialize);
    }
}
