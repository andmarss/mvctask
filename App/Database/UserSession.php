<?php

namespace App\Database;

use App\Database\Model;

class UserSession extends Model
{
    public static $table = 'users_session';

    protected static $fillable = [
        'user_id',
        'hash'
    ];
}