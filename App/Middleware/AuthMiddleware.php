<?php

namespace Middleware;

use \App\Auth;
use App\Controllers\Request;

class AuthMiddleware
{
    protected $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * Проверяет, авторизирован ли пользователь
     * Если нет - переводит на страницу авторизации
     */

    public function handle()
    {
        if($this->auth->guest()){
            return redirect()->route('login-index');
        }
    }
}