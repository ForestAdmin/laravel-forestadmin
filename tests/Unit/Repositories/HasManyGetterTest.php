<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Repositories;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Repositories\HasManyGetter;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Comment;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Range;
use ForestAdmin\LaravelForestAdmin\Tests\Feature\Models\Tag;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeData;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\ScopeManagerFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Mockery as m;

/**
 * Class HasManyGetter
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasManyGetterTest extends TestCase
{
    use FakeData;
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

        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function testAllWithHasMany(): void
    {
        $this->getBook()->save();
        $this->getComments();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'comments', 1])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Comment::first(), $data->items()[0]);
    }

    /**
     * @return void
     */
    public function testAllWithBelongsToManyRelation(): void
    {
        $this->getBook()->save();
        $this->getRanges();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'ranges', 1])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Range::first()->getAttributes(), $data->items()[0]->getAttributes());
    }

    /**
     * @return void
     */
    public function testAllWithMorphManyRelation(): void
    {
        $this->getBook()->save();
        $this->getTags();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'tags', 1])
            ->makePartial();
        $data = $repository->all();

        $this->assertInstanceOf(LengthAwarePaginator::class, $data);
        $this->assertEquals(Tag::first()->getAttributes(), $data->items()[0]->getAttributes());
    }

    /**
     * @return void
     */
    public function testCount(): void
    {
        $this->getBook()->save();
        $this->getComments();

        $repository = m::mock(HasManyGetter::class, [Book::first(), 'comments', 1])
            ->makePartial();

        $this->assertEquals(Comment::count(), $repository->count());
    }
}
