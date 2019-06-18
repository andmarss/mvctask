<?php

/**
 * Class Request
 * @package App\Controllers
 */

namespace App\Controllers;

use App\File;
use App\Session;

/**
 * Class Request
 * @package App\Controllers
 *
 * Класс, работающий с url-адресами
 */

class Request
{

    protected $data = [];

    protected static $instance;

    protected $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @return string
     *
     * Возвращает чистый uri (убирает боковые слеши)
     */

    protected function uri()
    {
        return trim(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'
        );
    }

    /**
     * @return string
     *
     * Возвращает полный uri, включая доменное имя
     */

    protected function fullUriWithQuery()
    {
        return domain() . trim(
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * @return mixed
     *
     * Возвращает метод щапроса (GET или POST)
     */

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param $key
     * @return mixed
     */

    public function __get($key)
    {
        if(isset($this->data[$key])){
            return $this->data[$key];
        }
    }

    /**
     * @param $key
     * @param $value
     */

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $name
     * @return bool
     */

    function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param null $name
     * @return mixed
     *
     * Возвращает обхект get запроса, включающий в себя все свойства get запроса
     */

    protected function get($name = null)
    {
        if(isset($_GET[$name])) {
            return $_GET[$name];
        } else {
            foreach ($_GET as $key => $value) {
                static::$instance->{$key} = $value;
            }

            return static::$instance;
        }
    }

    /**
     * @param null $name
     * @return mixed
     *
     * Возвращает обхект post запроса, включающий в себя все свойства post запроса
     */

    protected function post($name = null)
    {
        if(isset($_POST[$name])) {
            return $_POST[$name];
        } else {
            foreach ($_POST as $key => $value) {
                static::$instance->{$key} = $value;
            }

            return static::$instance;
        }
    }

    protected function getData()
    {
        return $this->data;
    }

    /**
     * @param $name
     * @return \App\UploadFile
     *
     * Возвращает объект изображения
     */

    public function file($name)
    {
        return (new \App\UploadFile($_FILES[$name]));
    }

    public function session()
    {
        return $this->session;
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
