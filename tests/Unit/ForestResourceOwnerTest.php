<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ForestAdmin\LaravelForestAdmin\Auth\OAuth2\ForestResourceOwner;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class ForestProviderTest
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestResourceOwnerTest extends TestCase
{
    /**
     * @var ForestResourceOwner
     */
    private ForestResourceOwner $forestResourceOwner;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->forestResourceOwner = new ForestResourceOwner(
            [
                'type'                              => 'users',
                'id'                                => '1',
                'first_name'                        => 'John',
                'last_name'                         => 'Doe',
                'email'                             => 'jdoe@forestadmin.com',
                'teams'                             => [
                    0 => 'Operations'
                ],
                'tags'                              => [
                    0 => [
                        'key'   => 'demo',
                        'value' => '1234',
                    ],
                ],
                'two_factor_authentication_enabled' => false,
                'two_factor_authentication_active'  => false,
                'permission_level'                  => 'admin',
            ],
            1234
        );
    }

    /**
     * @return void
     */
    public function testGetId(): void
    {
        $this->assertEquals(1, $this->forestResourceOwner->getId());
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $this->assertIsArray($this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('id', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('email', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('first_name', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('last_name', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('teams', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('tags', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('two_factor_authentication_enabled', $this->forestResourceOwner->toArray());
        $this->assertArrayHasKey('two_factor_authentication_active', $this->forestResourceOwner->toArray());
    }

    /**
     * @return void
     */
    public function testExpirationInSeconds(): void
    {
        $this->assertIsInt($this->forestResourceOwner->expirationInSeconds());
    }

    /**
     * @return void
     */
    public function testMakeJwt(): void
    {
        $result = JWT::decode($this->forestResourceOwner->makeJwt(), new Key(config('forest.api.auth-secret'), 'HS256'));

        $this->assertEquals(1, $result->id);
    }
}
