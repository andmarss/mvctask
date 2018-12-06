<?php
/**
 * Created by PhpStorm.
 * User: delux
 * Date: 29.11.2018
 * Time: 16:29
 */

namespace App;


class UploadFile
{
    protected $file;

    protected static $instance;

    public function __construct($file = null)
    {
        $this->file = (object) $file;
    }

    /**
     * @return string
     *
     * Возвращает имя файла
     */

    protected function getClientOriginalName()
    {
        return basename($this->file->name);
    }

    /**
     * @return mixed
     *
     * Возвращает формат файла
     */

    public function getClientOriginalExtension()
    {
        return pathinfo($this->file->name, PATHINFO_EXTENSION);
    }

    /**
     * @return mixed
     *
     * Возвращает тип файла
     */

    protected function getType()
    {
        return $this->file->tupe;
    }

    /**
     * @return mixed
     *
     * Возвращает размер файла (в байтах)
     */

    protected function getSize()
    {
        return $this->file->size;
    }

    /**
     * @param string $directory
     * @param null $name
     * @return bool
     * @throws \Exception
     *
     * Размещает файл в указанной директории
     */

    protected function move($directory = '', $name = null)
    {
        $uploaddir = $this->contentDir() . trim( preg_replace('/\//', DIRECTORY_SEPARATOR, $directory), '/') . DIRECTORY_SEPARATOR;

        if(!file_exists($uploaddir) && !is_dir($uploaddir)) {
            mkdir($uploaddir, 0755, true);
        }

        $uploadfile = $uploaddir . basename(!is_null($name) ? $name : $this->file->name);

        $moved = move_uploaded_file($this->file->tmp_name, $uploadfile);

        if (!$moved) {
            throw new \Exception('Не получилось загрузить файл ' . !is_null($name) ? $name : $this->file->name);
        }

        return $moved;
    }

    /**
     * @return array
     *
     * Возвращает массив, содержащий данные по длине и ширине переданного изображения
     */

    protected function size()
    {
        if(isset($this->file)) {
            [$width, $height] = getimagesize($this->file->tmp_name);

            return [$width, $height];
        } else {
            return [null, null];
        }
    }

    /**
     * @return null|string
     *
     * Возвращаен формат файла
     */

    protected function extension()
    {
        return $this->file ? strtolower(pathinfo($this->file->name, PATHINFO_EXTENSION)) : null;
    }

    /**
     * @param array $config
     * @param $path
     * @param string $name
     * @return mixed|null
     *
     * Принимает массив с ключами width и height, до которых должно быть ужато изображение
     * После того, как размер файла изменен, размещает его по указанному пути
     * Работает только с форматами jpeg, gif и png
     */

    protected function resizeAndMove(array $config = [], $path, $name = '')
    {
        if($this->file) {
            $type = $this->type();
            $uploadPath = $this->contentDirWithoutRootSystem() . trim( preg_replace('/\//', DIRECTORY_SEPARATOR, $path), '/') . DIRECTORY_SEPARATOR;
            $pathWithFileName = $uploadPath . ($name ? $name : time()) . '.' . $this->extension();

            if(!file_exists($this->pathToProjectRootWithSystemPath() . $uploadPath) && !is_dir($this->pathToProjectRootWithSystemPath() . $uploadPath)) {
                mkdir($this->pathToProjectRootWithSystemPath() . $uploadPath, 0755, true);
            }

            switch ($type) {
                case IMAGETYPE_JPEG:
                    $resourceType = imagecreatefromjpeg($this->file->tmp_name);
                    $imageLayer = $this->createLayer($resourceType, $config);
                    imagejpeg($imageLayer, $this->pathToProjectRootWithSystemPath() . $pathWithFileName);
                    break;

                case IMAGETYPE_GIF:
                    $resourceType = imagecreatefromgif($this->file->tmp_name);
                    $imageLayer = $this->createLayer($resourceType, $config);
                    imagegif($imageLayer, $this->pathToProjectRootWithSystemPath() . $pathWithFileName);
                    break;

                case IMAGETYPE_PNG:
                    $resourceType = imagecreatefrompng($this->file->tmp_name);
                    $imageLayer = $this->createLayer($resourceType, $config);
                    imagepng($imageLayer, $this->pathToProjectRootWithSystemPath() . $pathWithFileName);
                    break;
            }

            return preg_replace('/\/content\//','',preg_replace('/\\\\/', '/', $pathWithFileName)); // удаляем \content, что бы функция asset потом могла добавить этот файл на страницу
        } else {
            return null;
        }
    }

    /**
     * @return string
     *
     * Возвращает полный путь к папке content
     */

    protected function contentDir()
    {
        return $this->pathToProjectRootWithSystemPath() . $this->contentDirWithoutRootSystem();
    }

    /**
     * @return string
     *
     * Возвращает путь к папке content внутри проекта
     */

    protected function contentDirWithoutRootSystem()
    {
        return DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     *
     * Возвращает полный путь к корню проекта
     */

    protected function pathToProjectRootWithSystemPath()
    {
        return dirname(__DIR__);
    }

    /**
     * @param $resourceType
     * @param array $config
     * @return resource
     *
     * Возвращает шаблон для изображения, размер которого нужно изменить
     * По умолчанию уменьшает шаблон изображения до 320px в ширину и до 240px в высоту
     */

    private function createLayer($resourceType, array $config = [])
    {
        $width = isset($config['width']) ? ($config['width'] > 320 ? 320 : $config['width']) : 320;
        $height = isset($config['height']) ? ($config['height'] > 240 ? 240 : $config['height']) : 240;
        [$imgWidth, $imgHeight] = $this->size();
        $layer = imagecreatetruecolor($width, $height);
        imagecopyresampled($layer, $resourceType, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

        return $layer;
    }

    /**
     * @return null
     *
     * Возвращает тип файла
     */

    protected function type()
    {
        return $this->file ? getimagesize($this->file->tmp_name)[2] : null;
    }

    public static function __callStatic($method, $args)
    {
        static::$instance = new static();

        return static::$instance->$method(...$args);
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }
    }
}