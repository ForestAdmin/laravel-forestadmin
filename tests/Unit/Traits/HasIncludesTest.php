<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit\Traits;

use ForestAdmin\LaravelForestAdmin\Services\Concerns\HasIncludes;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use ForestAdmin\LaravelForestAdmin\Tests\Utils\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use ReflectionFunction;

/**
 * Class HasIncludesTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class HasIncludesTest extends TestCase
{
    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAddInclude(): void
    {
        $trait = $this->getObjectForTrait(HasIncludes::class);

        $values = $this->invokeMethod($trait, 'addInclude', ['foo', ['a', 'b', 'c'], 'foo_key']);

        $this->assertIsArray($values->getIncludes());
        $this->assertEquals(['fields' => 'a,b,c', 'foreign_key' => 'foo_key'], $values->getIncludes()['foo']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAppendRelationEagerLoadAllFields(): void
    {
        $trait = $this->getObjectForTrait(HasIncludes::class);
        $query = Book::with('category')
            ->select('books.label,books.comment,books.difficulty');
        $includes = [
            'category' => [
                'fields' => 'categories.id',
                'foreign_key' => 'books.category_id',
            ]
        ];
        $appendQuery = $this->invokeMethod($trait, 'appendRelations', [$query, $includes]);
        $result = (new ReflectionFunction($appendQuery->getEagerLoads()['category']))->getStaticVariables();

        $this->assertEquals('category:categories.*', $result['name']);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testAppendRelationEagerMergeFields(): void
    {
        $trait = $this->getObjectForTrait(HasIncludes::class);
        $query = Book::with('category:label')
            ->select('books.label,books.comment,books.difficulty');
        $includes = [
            'category' => [
                'fields' => 'categories.id',
                'foreign_key' => 'books.category_id',
            ]
        ];
        $appendQuery = $this->invokeMethod($trait, 'appendRelations', [$query, $includes]);
        $result = (new ReflectionFunction($appendQuery->getEagerLoads()['category']))->getStaticVariables();

        $this->assertEquals('category:categories.label,categories.id', $result['name']);
    }
    //    protected function appendRelations(Builder $query, array $includes): Builder
    //    {
    //        /** @var \Closure $closure */
    //        $eagerLoads = $query->getEagerLoads();
    //        foreach ($includes as $key => $value) {
    //            if ($value['foreign_key']) {
    //                $query->addSelect($value['foreign_key']);
    //            }
    //
    //            if (isset($eagerLoads[$key])) {
    //                $with = (new ReflectionFunction($closure))->getStaticVariables();
    //                $relation = $query->getModel()->$key();
    //                $relationTable = $relation->getRelated()->getTable();
    //                $fieldsRelationEagerLoad = explode(',', Str::after($with['name'], "$key:"));
    //                $includeFields = explode(',', Str::replace("$relationTable.", '', $value['fields']));
    //                $value['fields'] = $fieldsRelationEagerLoad !== '' ? collect($fieldsRelationEagerLoad)->merge($includeFields)->map(fn ($field) => "$relationTable.$field")->implode(',') : '*';
    //            }
    //
    //            $query->with($key . ':' . $value['fields']);
    //        }
    //
    //        return $query;
    //    }
}
