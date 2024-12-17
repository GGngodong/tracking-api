<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $username
 * @property string $password
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value)
 */
class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $fillable = [
        'username',
        'email',
        'password'
    ];

    public function permitLetters(): HasMany
    {
        return $this->hasMany(PermitLetter::class, 'user_id', 'id');
    }

}
