<?php

namespace ForestAdmin\LaravelForestAdmin\Auth\OAuth2;

use Firebase\JWT\JWT;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * Class ForestResourceOwner
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class ForestResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var int
     */
    private int $renderingId;

    /**
     * @param array $data
     * @param int   $renderingId
     */
    public function __construct(array $data, int $renderingId)
    {
        $this->data = $data;
        $this->renderingId = $renderingId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->getValueByKey($this->data, 'id');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * @return int
     */
    public function expirationInSeconds(): int
    {
        return (new \DateTime())
            ->modify('+ 1 hour')
            ->format('U');
    }

    /**
     * @return string
     */
    public function makeJwt(): string
    {
        $user = [
            'id'               => $this->data['id'],
            'email'            => $this->data['email'],
            'first_name'       => $this->data['first_name'],
            'last_name'        => $this->data['last_name'],
            'team'             => $this->data['teams'][0],
            'tags'             => $this->data['tags'],
            'rendering_id'     => $this->renderingId,
            'exp'              => $this->expirationInSeconds(),
            'permission_level' => $this->data['permission_level'],
        ];

        return JWT::encode($user, config('forest.api.auth-secret'), 'HS256');
    }
}
