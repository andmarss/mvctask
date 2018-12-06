<?php

/**
 * Class User
 */

namespace App\Database;

class User extends Model
{
    public static $table = 'users';

    protected static $fillable = [
        'name',
        'email',
        'password',
        'admin',
        'phone',
        'created_at'
    ];

    public function tasks()
    {
        return $this->hasMany('Task', 'user_id', 'id');
    }

    public function sessions()
    {
        return $this->hasMany('UserSession', 'user_id', 'id');
    }
}