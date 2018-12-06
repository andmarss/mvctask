<?php

namespace App;

use App\Session;
use App\App;

/**
 * Class Token
 * @package App
 *
 * Класс для работы с токенами, используемыми для csrf-безопасности
 */

class Token
{
    protected static $instance;

    /**
     * @param string $token_name
     * @return mixed|null
     *
     * Генерирует token
     */

    protected function generate($token_name = '')
    {
        return Session::exists($token_name ? $token_name : App::get('config/session/token_name' )) ? Session::get( $token_name ? $token_name : App::get('config/session/token_name' )) :  Session::put( $token_name ? $token_name : App::get('config/session/token_name' ) , md5(uniqid()));
    }

    /**
     * @param $token
     * @return bool
     *
     * Проверяет, совпадает ли текущий токен с токеном, содержащимся в сессии
     */

    protected function check($token)
    {
        $tokenName = App::get('config/session/token_name');

        if(Session::exists($tokenName) && $token === Session::get($tokenName)) {
            Session::delete($tokenName);

            return true;
        }

        return false;
    }

    public static function __callStatic($method, $args)
    {
        if(!is_object(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance->$method(...$args);
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }
    }
}