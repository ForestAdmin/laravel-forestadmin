<?php

namespace ForestAdmin\LaravelForestAdmin\Tests\Utils\Models;

use ForestAdmin\LaravelForestAdmin\Tests\Utils\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @package Laravel-forestadmin
 * @license GNU https://www.gnu.org/licenses/licenses.html
 * @link    https://github.com/ForestAdmin/laravel-forestadmin
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return UserFactory
     */
    protected static function newFactory()
    {
        return new UserFactory();
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
