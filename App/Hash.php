<?php

namespace App;

/**
 * Class Hash
 * @package App
 *
 * Класс для генерации hash-строк, и работы с ними
 */

class Hash
{
    protected static $instance;

    /**
     * @param $value
     * @param string $salt
     * @return string
     *
     * Генирирует hash-строку
     */

    protected function create($value, $salt = '')
    {
        return hash('sha256', $value . $salt);
    }

    /**
     * @return string
     *
     * Генерирует соль, для работы с hash строками
     */

    protected function salt()
    {
        return bin2hex(random_bytes(20));
    }

    /**
     * @return string
     *
     * Генерирует уникальную hash-строку
     */

    protected function unique()
    {
        return $this->create(uniqid(), $this->salt());
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