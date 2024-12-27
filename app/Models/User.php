<?php

namespace App\Models;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


/**
 * @property string $username
 * @property string $password
 * @property string $email
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value)
 * @method static create(array $array)
 * @method static first()
 *
 */
class User extends Model implements Authenticatable
{
    use HasApiTokens;
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $fillable = [
        'username',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password'
    ];

    public function getAuthIdentifierName(): string
    {
        return 'email';
    }

    public function getAuthIdentifier(): string
    {
        return $this->email;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getRememberToken(): string
    {
        return $this->token;
    }

    public function setRememberToken($value): void
    {
        $this->token = $value;
    }

    public function getRememberTokenName(): string
    {
        return 'token';
    }
}
