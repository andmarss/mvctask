<?php
/**
 * Created by PhpStorm.
 * User: delux
 * Date: 26.11.2018
 * Time: 14:18
 */

namespace App;


use App\Controllers\Request;
use App\Controllers\Router;

/**
 * Class Redirect
 * @package App
 *
 * Класс для работы с перенаправлениями
 */

class Redirect
{
    protected $path;
    protected $data;

    public function __construct($path = '', $data = [])
    {
        if($path !== '') {
            $this->path = $path;
            $this->to($path, $data);
        }

        Session::flash('old', Request::{Request::method()}());

        return $this;
    }

    /**
     * @param $path
     * @param array $data
     *
     * На какую страницу будет переведен пользователь
     */

    public function to($path, $data = [])
    {
        if($data) {
            Session::put('redirect', $data);
        }

        if(is_numeric($path)) {
            switch ((int) $path) {
                case 404:
                    header('HTTP/1.1 404 Not Found');
                    view('errors/404', ['code' => 404, 'content' => 'Искомый файл не был найден']);
                    exit();
                break;
            }
        }

        $url = domain() . '/' . trim( parse_url($path, PHP_URL_PATH), '/' );
        header("Location: ${url}");
        exit();
    }

    /**
     * @param array $data
     *
     * Возвращает пользователя на страницу, с которой был произведен запрос
     */

    public function back($data = [])
    {
        if($data) {
            Session::put('redirect', $data);
        }

        $url = !is_null($_SERVER['HTTP_REFERER']) ? domain() . '/' . trim( parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH), '/' ) : '/';
        header("Location: ${url}");
        exit();
    }

    /**
     * @param $name
     * @param array $data
     * @throws \Exception
     *
     * Перенаправлят пользователя по имени маршрута
     */

    public function route($name, $data = [])
    {
        $url = Router::convertUri($name, $data);

        return $this->to($url, $data);
    }
}