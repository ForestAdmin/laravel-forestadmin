<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Unit;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use ForestAdmin\LaravelForestAdmin\Auth\Guard\Model\ForestUser;
use ForestAdmin\LaravelForestAdmin\Tests\TestCase;

/**
 * Class ForestUserTest
 *
 * @package  Laravel-forestadmin
 * @license  GNU https://www.gnu.org/licences/licences.html
 * @link     https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestUserTest extends TestCase
{
    /**
     * @return void
     */
    public function testSetPermissions(): void
    {
        $forestUser = new ForestUser([]);
        $permissions = collect(
            [
                'book' => [
                    'browseEnabled',
                    'readEnabled',
                    'editEnabled',
                    'addEnabled',
                    'deleteEnabled',
                    'exportEnabled',
                ],
            ]
        );
        $forestUser->setPermissions($permissions);

        $this->assertEquals($permissions, $forestUser->getPermissions());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetStats(): void
    {
        $forestUser = new ForestUser([]);
        $stats = [
            'queries'      => [],
            'leaderboards' => [],
            'lines'        => [],
            'objectives'   => [],
            'percentages'  => [],
            'pies'         => [],
            'values'       => [],
        ];
        $forestUser->setStats($stats);

        $this->assertEquals($stats, $forestUser->getStats());
    }

    /**
     * @return void
     * @throws SchemaException
     * @throws Exception
     */
    public function testSetSmartActionPermissions(): void
    {
        $forestUser = new ForestUser([]);
        $smartActionPermissions = collect(
            [
                'book' => [
                    'My smart action',
                ],
            ]
        );
        $forestUser->setSmartActionPermissions($smartActionPermissions);

        $this->assertEquals($smartActionPermissions, $forestUser->getSmartActionPermissions());
    }

    /**
     * @return void
     */
    public function testHasPermissions(): void
    {
        $forestUser = new ForestUser([]);
        $permissions = collect(
            [
                'book' => [
                    'browseEnabled',
                    'readEnabled',
                    'editEnabled',
                    'addEnabled',
                    'deleteEnabled',
                    'exportEnabled',
                ],
            ]
        );
        $forestUser->setPermissions($permissions);

        $this->assertTrue($forestUser->hasPermission('book', 'browseEnabled'));
    }

    /**
     * @return void
     */
    public function testHasSmartActionPermission(): void
    {
        $forestUser = new ForestUser([]);
        $smartActionPermissions = collect(
            [
                'book' => [
                    'My smart action',
                ],
            ]
        );
        $forestUser->setSmartActionPermissions($smartActionPermissions);

        $this->assertTrue($forestUser->hasSmartActionPermission('book', 'My smart action'));
    }

    /**
     * @return void
     */
    public function testAddPermission(): void
    {
        $forestUser = new ForestUser([]);
        $forestUser->setPermissions(collect([]));
        $permissions = [
            'browseEnabled',
            'readEnabled',
            'editEnabled',
            'addEnabled',
            'deleteEnabled',
            'exportEnabled',
        ];
        $forestUser->addPermission('book', $permissions);
        $expected = collect(
            [
                'book' => $permissions,
            ]
        );

        $this->assertEquals($expected, $forestUser->getPermissions());
    }

    /**
     * @return void
     */
    public function testAddSmartActionPermission(): void
    {
        $forestUser = new ForestUser([]);
        $smartActionPermissions = ['My smart action'];
        $forestUser->setSmartActionPermissions(collect([]));
        $forestUser->addSmartActionPermission('book', $smartActionPermissions);
        $expected = collect(
            [
                'book' => $smartActionPermissions,
            ]
        );

        $this->assertEquals($expected, $forestUser->getSmartActionPermissions());
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testFormatChartPayload(): void
    {
        $forestUser = new ForestUser([]);
        $chartPayload = [
            'type'            => 'Value',
            'collection'      => 'book',
            'query'           => null,
            'aggregate'       => 'Count',
            'filters'         => "{\"field\":\"label\",\"operator\":\"equal\",\"value\":\"foo\"}"
        ];
        $chartPayloadFormated = [
            'type'               => 'Value',
            'sourceCollectionId' => 'book',
            'aggregator'         => 'Count',
            'filter'             => "{\"field\":\"label\",\"operator\":\"equal\",\"value\":\"foo\"}"
        ];

        $result = $this->invokeMethod($forestUser, 'formatChartPayload', [$chartPayload]);

        $this->assertIsArray($result);
        $this->assertEquals($chartPayloadFormated, $result);
    }
}
