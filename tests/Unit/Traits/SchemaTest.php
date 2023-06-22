<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Exceptions\ForestException;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\FakeSchema;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Utils\Traits\Schema;
use Illuminate\Support\Facades\File;

/**
 * Class SchemaTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class SchemaTest extends TestCase
{
    use FakeSchema;

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetModel(): void
    {
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $trait = $this->getObjectForTrait(Schema::class);
        $dummyModel = 'Book';
        $getModel = $this->invokeMethod($trait, 'getModel', [$dummyModel]);

        $this->assertEquals(Book::class, get_class($getModel));
    }

    /**
     * @throws \ReflectionException
     * @return void
     */
    public function testGetModelException(): void
    {
        File::shouldReceive('get')->andReturn($this->fakeSchema(true));
        $trait = $this->getObjectForTrait(Schema::class);
        $dummyModel = 'Foo';

        $this->expectException(ForestException::class);
        $this->expectExceptionMessage("🌳🌳🌳 No model found for collection $dummyModel");

        $this->invokeMethod($trait, 'getModel', [$dummyModel]);
    }
}
