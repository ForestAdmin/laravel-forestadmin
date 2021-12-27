<?php

namespace ForestAdmin\LaravelForestAdmin\Tests;

use ForestAdmin\LaravelForestAdmin\ForestServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Class TestCase
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class TestCase extends OrchestraTestCase
{
    /**
     * Call protected/private method of a class.
     * @param object $object
     * @param string $methodName
     * @param array  $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeMethod(object &$object, string $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Call protected/private property of a class.
     * @param object $object
     * @param string $methodName
     * @param null   $setData
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeProperty(object &$object, string $methodName, $setData = null)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($methodName);
        $property->setAccessible(true);

        if ($setData) {
            $property->setValue($object, $setData);
        }

        return $property->getValue($object);
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $db = new DB();
        $db->addConnection(
            [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]
        );

        $db->setAsGlobal();
        $db->bootEloquent();
        $this->migrate();
    }

    /**
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $config = $app['config'];
        $config->set('app.debug', true);
        $config->set('forest.api.secret', 'my-secret-key');
        $config->set('forest.api.auth-secret', 'auth-secret-key');
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ForestServiceProvider::class,
        ];
    }

    /**
     * Make some dummy tables
     * @return void
     */
    protected function migrate(): void
    {
        DB::schema()->create(
            'users',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'products',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->decimal('price');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'categories',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'books',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->text('comment');
                $table->enum('difficulty', ['easy', 'hard']);
                $table->float('amount', 8, 2);
                $table->boolean('active')->default(true);
                $table->jsonb('options');
                $table->string('other')->default('N/A');
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'ranges',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'book_range',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->foreignId('range_id')->constrained()->onDelete('cascade');
            }
        );

        DB::schema()->create(
            'comments',
            function (Blueprint $table) {
                $table->id();
                $table->string('body');
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'companies',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'bookstores',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'authors',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->table(
            'users',
            function (Blueprint $table) {
                $table->foreignId('author_id')->nullable()->constrained()->onDelete('SET NULL');
            }
        );

        DB::schema()->create(
            'editors',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'tags',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->morphs('taggable');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'images',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url');
                $table->morphs('imageable');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'buys',
            function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'buyables',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId('buy_id')->constrained();
                $table->integer('buyable_id');
                $table->string('buyable_type');
            }
        );

        DB::schema()->create(
            'movies',
            function (Blueprint $table) {
                $table->id();
                $table->string('body');
                $table->foreignId('book_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamps();
            }
        );

        DB::schema()->create(
            'sequels',
            function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->nullableMorphs('sequelable');
                $table->timestamps();
            }
        );
    }
}
