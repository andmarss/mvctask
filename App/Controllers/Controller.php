<?php
/**
 * Created by PhpStorm.
 * User: delux
 * Date: 26.11.2018
 * Time: 13:26
 */

namespace App\Controllers;

use App\Session;
use App\Validate;

/**
 * Class Controller
 * @package App\Controllers
 *
 * Класс, от которого наследуются остальные контроллеры
 */

class Controller
{
    protected $validator;

    protected static $errors;

    protected static $passed;

    public function __construct()
    {
        $this->validator = new Validate();
    }

    /**
     * @param $request
     * @param array $data
     *
     * Используется для валидации запроса
     * Если запрос проходит проверку - запрос выполняется дальше
     * В противном случае - возвращает пользователя обратно, информируя об ошибках
     */

    protected function validate($request, $data = [])
    {
        $this->validator->validate($request, $data);

        if(!$this->validator->passed()) {
            Session::put('error', $this->validator->getErrors());

            redirect()->back(['errors' => $this->validator->getErrors()]);
        }
    }
}