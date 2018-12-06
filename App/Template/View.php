<?php

namespace App;


class View
{
    protected $data = [];

    protected $path;

    protected $tpl;

    public function __construct($path, $data = [])
    {
        $this->path = $path;
        $this->data = $data;

        $this->tpl = new \App\Template();
    }

    /**
     * @param array $data
     * @return $this
     *
     * мерджит новые данные с уже имеющимися
     */

    public function with(array $data = [])
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * @return mixed
     *
     * Получить текущий путь файла
     */

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     * @return $this
     *
     * Установить путь к файлу
     */

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     *
     * Возвращает HTML-контент
     */

    public function get()
    {
        $data = $this->data;

        if(\App\Session::has('redirect')) {
            $redirect = \App\Session::get('redirect');

            \App\Session::delete('redirect');

            $data = array_merge($data, $redirect);
        }

        if(\App\Session::has('validator-errors')) {
            $errors = \App\Session::get('validator-errors');

            \App\Session::delete('validator-errors');

            $data = array_merge($data, ['errors' => \collect($errors)]);
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $this->path;
        } catch (\Exception $e) {
            ob_get_clean(); throw $e;
        }

        return ob_get_clean();
    }

    /**
     * @return string
     *
     * Получаем путь к исходному файлу, и данными, которые были переданы
     * Парсим, превращая в валидный PHP код
     * Выводим HTML контент
     */

    public function render()
    {
        $this->path = $this->tpl->parse($this->path, $this->data);

        return $this->get();
    }

    /**
     * @param $view
     * @param array $data
     * @return static
     *
     * Статический метод для создания экземпляра
     */

    public static function make($view, $data = [])
    {
        return new static($view, $data);
    }

    public function __toString()
    {
        return $this->render();
    }
}