<?php

namespace App;

/**
 * Class Cookie
 * @package App
 *
 * Класс для работы с куками
 */

class Cookie
{
    protected static $instance;

    /**
     * @param $name
     * @return bool
     *
     * Проверяет, есть ли в cookies элемент с переданым ключом
     */

    protected function exists($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * @param $name
     * @return bool
     *
     * Проверяет, есть ли в cookies элемент с переданым ключом
     */

    protected function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * @param $name
     * @return bool
     *
     * Возвращает элемент по ключу
     */

    protected function get($name)
    {
        return $this->has($name) ? $_COOKIE[$name] : false;
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @return bool
     *
     * Сохраняет элемент по ключу
     */

    protected function put($name, $value, $expire = 86400)
    {
        if(setcookie($name, $value, time() + $expire, '/')) {
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return bool
     *
     * Удаляет элемент по ключу
     */

    protected function delete($name) {
        if($this->has($name)) {
            return $this->put($name, '', -1);
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