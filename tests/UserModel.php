<?php

/**
 * Users
 *
 * @property int4      $id
 * @property varchar   $name
 * @property varchar   $email
 * @property varchar   $password
 * @property varchar   $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserModel extends \Eloquent
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
