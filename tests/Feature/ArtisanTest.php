<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Feature;

use ForestAdmin\LaravelForestAdmin\Listeners\ArtisanStart;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;

/**
 * Class ArtisanTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ArtisanTest extends TestCase
{
    /**
     * @return void
     */
    public function testArtisanStarted(): void
    {
        Event::fake();

        Event::assertListening(
            CommandStarting::class,
            ArtisanStart::class
        );
    }
}
