<?php
/**
 * Created by PhpStorm.
 * User: Zver
 * Date: 26.11.2018
 * Time: 0:03
 */

namespace App;

use App\App;
use App\Database\DB;

class Validate
{
    protected $pdo;
    protected static $instance;
    protected static $errors = [];

    public function __construct()
    {
        $this->pdo = App::get('database')->get_dbh();
    }

    /**
     * @param $request
     * @param array $items
     * @return $this
     *
     * Функция принимает объект Router'а, и сравнивает, совпадают ли переданные в запросе данные с теми
     * что указаны в правилах массива items
     */

    protected function validate($request, $items = [])
    {
        $this->clearErrors();

        foreach ($items as $item => $rules) {
            foreach ($rules as $rule => $rule_value) {

                $value = $request->{$item};

                switch ($rule) {
                    case 'required' && empty(trim($value)):
                        $this->addError($item,"Поле {$item} обязательно для заполнения");
                        break;
                    case 'min':
                        if(mb_strlen(trim($value)) < $rule_value) {
                            $this->addError($item,"\"{$item}\" должно быть больше {$rule_value} знаков.");
                        }
                        break;
                    case 'max':
                        if(mb_strlen(trim($value)) > $rule_value) {
                            $this->addError($item,"\"{$item}\" должно быть меньше {$rule_value} знаков.");
                        }
                        break;
                    case 'matches':
                        if (trim($value) !== $request->{$rule_value}) {
                            $this->addError($item,"\"{$item}\" должно быть совпадать с {$rule_value}.");
                        }
                        break;
                    case 'unique':
                        $check = DB::table($rule_value)->where([$item => $value])->first();

                        if ($check) {
                            $this->addError($item,"Пользователь с указанными данными: \"{$value}\", уже существует.");
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($item,"\"{$item}\" должно быть валидным email-адресом.");
                        }
                        break;
                    case 'int':
                        if (!filter_var((int) $value, FILTER_VALIDATE_INT)) {
                            $this->addError($item,"\"{$item}\" должно иметь числовое значение.");
                        }
                        break;
                }
            }
        }

        return $this;
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

    /**
     * @param $item
     * @param $error
     *
     * Добавляет ошибку в массив по имени поля
     */

    private function addError($item, $error)
    {
        if(!isset(static::$errors[$item])) {
            static::$errors[$item] = [];
        }

        static::$errors[$item][] = $error;
    }

    /**
     * @return array
     *
     * Получить весь массив ошибок
     */

    protected function getErrors()
    {
        return static::$errors;
    }

    /**
     * Очищает массив ошибок
     */

    protected function clearErrors()
    {
        static::$errors = [];
    }

    /**
     * @return bool
     *
     * Проверяет, есть ли ошибки в щапросе
     */

    protected function passed()
    {
        return count(static::$errors) === 0;
    }
}