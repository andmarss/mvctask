<?php

/**
 * Class App
 */

namespace App;

/**
 * Class App
 * @package App
 *
 * Управляющий класс
 */

class App
{
    protected static $registry = [];

    /**
     * @param $key
     * @param $value
     *
     * Записывает управляющую переменную в массив
     */

    public static function bind($key, $value)
    {
        static::$registry[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     *
     * Разбивает строку $key по разделителю /
     * Возвращает значение, совпадающее с ключем key
     */

    public static function get($key)
    {
        $keys = explode('/', $key);
        $registry = static::$registry;

        foreach ($keys as $key) {
            $registry = isset($registry[$key]) && $registry ? $registry[$key] : false;
        }

        return $registry;
    }
}
