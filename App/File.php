<?php
/**
 * Created by PhpStorm.
 * User: delux
 * Date: 05.12.2018
 * Time: 10:58
 */

namespace App;


class File
{

    /**
     * @param $path
     * @return bool
     *
     * Проверка существования файла
     */

    public static function exists($path)
    {
        return file_exists($path);
    }

    /**
     * @param $path
     * @return bool|string
     *
     * Загрузить контент из файла, если существует файл
     */

    public static function get($path)
    {
        return static::exists($path) ? file_get_contents($path) : '';
    }

    /**
     * Записать данные в файл
     */

    public static function put($path, $data)
    {
        return file_put_contents($path, $data, LOCK_EX);
    }

    /**
     * Записать данные в конец файла (не перезатирать)
     */

    public static function append($path, $data)
    {
        return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
    }

    /**
     * Удалить файл
     */

    public static function delete($path)
    {
        if(static::exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Получить формат файла
     */

    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Получить тип
     */

    public static function type($path)
    {
        return filetype($path);
    }

    /**
     * Получить размер файла (в байтах)
     */

    public static function size($path)
    {
        return filesize($path);
    }

    /**
     * Вернуть время последнего изменения файла
     */

    public static function modified($path)
    {
        return filemtime($path);
    }

    /**
     * Путь к корню директории проекта
     */

    public static function root()
    {
        return dirname(__DIR__);
    }

    /**
     * Путь к папке views
     */

    public static function viewPath()
    {
        return static::root() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
    }

    /**
     * Путь к папке storage/cache
     */

    public static function cachePath()
    {
        return static::root() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param $path
     * @return string
     *
     * Возвращает не полный путь к файлу внутри папки cache
     */

    public static function getNotFullCompiledPath($path)
    {
        return 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . md5($path) . '.php';
    }

    /**
     * @param $path
     * @return string
     *
     * Получить полный путь к скомилированному файлу
     */

    public static function getCompiledPath($path)
    {
        return static::cachePath() . md5($path) . '.php';
    }

    /**
     * @param $path
     * @return string
     *
     * Получить полный путь к файлу
     */

    public static function getViewPath($path)
    {
        return static::viewPath() . $path;
    }

    /**
     * @param $path
     * @return bool
     *
     * Проверяет, изменился ли исходный файл по отношению к скомпилированному
     */

    public static function isExpired($path)
    {
        $compiled = static::getCompiledPath($path);

        if (!File::exists($compiled) )
        {
            return true;
        }

        return static::modified( $path ) >= static::modified( $compiled );
    }
}