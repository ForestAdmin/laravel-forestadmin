<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils;

use ForestAdmin\LaravelForestAdmin\Auth\Guard\ForestUserFactory;
use Mockery as m;

/**
 * Class MockClientHttp
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
trait MockForestUserFactory
{
    /**
     * @param bool $allowed
     * @return void
     */
    public function mockForestUserFactory(bool $allowed = true): void
    {
        $factory = m::mock(ForestUserFactory::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $factory->shouldReceive('getPermissions')
            ->andReturn($this->getPermissions($allowed));

        app()->instance(ForestUserFactory::class, $factory);
    }

    /**
     * @param bool $allowed
     * @return array
     */
    public function getPermissions(bool $allowed = true): array
    {
        $permissions = $allowed ? [1] : [];
        return [
            'stats'       => [
                'queries'      => [],
                'leaderboards' => [],
                'lines'        => [],
                'objectives'   => [],
                'percentages'  => [],
                'pies'         => [],
                'values'       => [],
            ],
            'meta'        => [
                'rolesACLActivated' => true,
            ],
            'collections' => [
                'advertisement' => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'author'        => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'book'          => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'bookstore'     => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'buy'           => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'category'      => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'comment'       => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'company'       => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'editor'        => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'image'         => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'movie'         => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'product'       => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'range'         => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'sequel'        => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'tag'           => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
                'user'          => [
                    'collection' => [
                        'browseEnabled' => $permissions,
                        'readEnabled'   => $permissions,
                        'editEnabled'   => $permissions,
                        'addEnabled'    => $permissions,
                        'deleteEnabled' => $permissions,
                        'exportEnabled' => $permissions,
                    ],
                    'actions'    => [],
                ],
            ],
            'renderings'  => [
                108 => [
                    'advertisement' => ['scope' => null, 'segments' => [],],
                    'author'        => ['scope' => null, 'segments' => [],],
                    'book'          => ['scope' => null, 'segments' => [],],
                    'bookstore'     => ['scope' => null, 'segments' => [],],
                    'buy'           => ['scope' => null, 'segments' => [],],
                    'category'      => ['scope' => null, 'segments' => [],],
                    'comment'       => ['scope' => null, 'segments' => [],],
                    'company'       => ['scope' => null, 'segments' => [],],
                    'editor'        => ['scope' => null, 'segments' => [],],
                    'image'         => ['scope' => null, 'segments' => [],],
                    'movie'         => ['scope' => null, 'segments' => [],],
                    'product'       => ['scope' => null, 'segments' => [],],
                    'range'         => ['scope' => null, 'segments' => [],],
                    'sequel'        => ['scope' => null, 'segments' => [],],
                    'tag'           => ['scope' => null, 'segments' => [],],
                    'user'          => ['scope' => null, 'segments' => [],],
                ],
            ],
        ];
    }
}