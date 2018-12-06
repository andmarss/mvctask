<?php

/**
 * Class Post
 * @package App\Database
 */

namespace App\Database;

class Task extends Model
{
    public static $table = 'tasks';

    protected static $fillable = [
        'title',
        'content',
        'slug',
        'picture',
        'created_at',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo( 'User' , 'id', 'user_id');
    }
}