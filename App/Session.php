<?php

namespace App;

/**
 * Class Session
 * @package App
 *
 * Класс для работы с сессиями
 */

class Session
{
    protected static $instance;

    /**
     * @param $key
     * @param $value
     * @return mixed
     *
     * Добавляет элемент в сессию
     */

    protected function put($key, $value)
    {
        $_SESSION[$key] = $value;

        return $_SESSION[$key];
    }

    /**
     * @param $key
     * @return bool
     *
     * Проверяет, имеется ли элемент с переданным ключом в сессии
     */

    protected function exists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param $key
     * @return bool
     *
     * Проверяет, имеется ли элемент с переданным ключом в сессии
     */

    protected function has($key)
    {
        return $this->exists($key);
    }

    /**
     * @param $name
     * @param string $data
     * @return bool|null
     *
     * Если было передано два значения
     * Сохраняет в сессию по имени name значение value
     * Если передан одно name - возвращает значение из сессии, после чего удаляет его
     */

    protected function flash($name, $data = '')
    {
        if($this->exists($name)) {
            $value = $this->get($name);

            $this->delete($name);

            return $value;
        } else {
            $this->put($name, $data);
        }

        return true;
    }

    /**
     * @param $name
     * @return null
     *
     * Возвращает элемент по имени
     * Или null - если его нет
     */

    protected function get($name)
    {
        return $this->exists($name) ? $_SESSION[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     *
     * Очищает сессию по ключу
     */

    protected function delete($name)
    {
        unset($_SESSION[$name]);

        return true;
    }

    protected function token()
    {
        return (new Token());
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