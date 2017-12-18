<?php

namespace App\Models;

/**
 * Users
 *
 * @property integer   $id
 * @property string    $name
 * @property string    $email
 * @property string    $password
 * @property string    $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Users extends \Eloquent
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
    ];

    protected $guarded = ['id'];

}
