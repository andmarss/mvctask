<?php

namespace App;

use App\App;
use App\Controllers\Request;
use App\Database\DB;
use App\Database\User;
use App\Database\UserSession;

/**
 * Class Auth
 * @package App
 *
 * Класс для работы с аутентификацией
 */

class Auth
{
    protected $pdo;

    protected static $authorizedUser;

    protected static $instance;

    protected $sessionName;

    protected $cookieName;

    public function __construct()
    {
        $this->pdo = App::get('database')->get_dbh();
        $this->sessionName = App::get('config/session/session_name');
        $this->cookieName = App::get('config/remember/cookie_name');
    }

    /**
     * @param array $data
     * @return bool
     *
     * Проверяет, если пользователь по переданным свойствам есть - авторизирует его
     * Иначе возвращает false
     */

    protected function attempt($data = [])
    {
        $password = '';

        if(is_object($data) && $data instanceof Request) {
            $data = (array) $data->getData();
        }

        if(isset($data['token'])) {
            unset($data['token']);
        }

        if(isset($data['password'])) {
            $password = $data['password'];
            unset($data['password']);
        }

        $user = User::where($data)->first();

        if($user && password_verify($password, $user->password)) {
            $this->login($data);

            return true;
        } else {
            return false;
        }

    }

    /**
     * @param array $params
     * @param bool $remember
     * @return bool|mixed
     * @throws \Exception
     *
     * Метод для авторизации пользователя
     */

    protected function login($params = [], $remember = false)
    {
        if(is_object($params) && $params instanceof Request) {
            $params = (array) $params->getData();
        }

        if($params) {
            $password = '';

            if(isset($params['password'])) {
                $password = $params['password'];
                unset($params['password']);
            }

            if(isset($params['token'])) {
                unset($params['token']);
            }

            $email = $params['email'];

            $user = User::where(['email' => $email])->first();

            if ($user && ((password_verify($password, $user->password)) || $user->password === $password)) {
                Session::put($this->sessionName, $user->id);

                if($remember) {
                    $hashCheck = $user->sessions();
                    $hash = null;

                    if(!$hashCheck) {
                        $hash = Hash::unique();

                        UserSession::create([
                            'user_id' => $user->id,
                            'hash' => $hash
                        ]);
                    } else {
                        $hash = collect($hashCheck)->last();
                    }

                    Cookie::put($this->cookieName, $hash, App::get('config/remember/cookie_expire'));
                }

                return $user;
            } else {
                return false;
            }

        } else {
            if(App::get('DEV')) {
                throw new \Exception('Параметры для авторизации должны быть переданы в виде НЕ пустого массива, передан ' . gettype($params));
            } else {
                throw new \Exception('Упс... что-то пошло не так. Сообщите о проблеме администратору. Код ошибки: 1300' . gettype($params));
            }
        }
    }

    /**
     * @param array $params
     * @return bool|mixed|void
     * @throws \Exception
     *
     * Метод для регистрации пользователя
     */

    protected function register($params = [])
    {

        if(is_object($params) && $params instanceof Request) {
            $params = (array) $params->getData();
        }

        if($params) {
            $password = '';

            if(isset($params['password'])) {
                $password = $params['password'];
                unset($params['password']);
            }

            if(isset($params['token'])) {
                unset($params['token']);
            }

            $user = User::where($params)->first();

            if (!$user) {
                $params['password'] = password_hash($password, PASSWORD_BCRYPT);
                $params['created_at'] = date('Y-m-d H:i:s');

                User::create($params);

                $params['password'] = $password;

                return $this->login($params);
            } else {
                return redirect()->back(['errors' => 'Пользователь с таким Email\'ом уже существует']);
            }

        } else {
            if(App::get('DEV')) {
                throw new \Exception('Параметры для регистрации должны быть переданы в виде НЕ пустого массива, передан ' . gettype($params));
            } else {
                throw new \Exception('Упс... что-то пошло не так. Сообщите о проблеме администратору. Код ошибки: 1400' . gettype($params));
            }
        }
    }

    /**
     * @return mixed|null
     *
     * Если пользователь авторизирован - возвращает его объект
     * Иначе - возвращает null
     */

    protected function user()
    {
        $id = Session::get($this->sessionName);

        if($id) {
            return User::find($id);
        } else {
            return null;
        }
    }

    /**
     * @return null
     *
     * Возвращает id авторизированного пользователя
     */

    protected function id()
    {
        if ($this->check()) {
            return Session::get($this->sessionName);
        } else {
            return null;
        }
    }

    /**
     * @return bool
     *
     * Проверяет, авторизирован ли пользователь
     */

    protected function check()
    {
        return !is_null(Session::get($this->sessionName));
    }

    /**
     * @return bool
     *
     * Очищает информацию аутентификации пользователя в сессии пользователя
     */

    protected function logout()
    {
        if(Session::has($this->sessionName)) {
            Session::delete($this->sessionName);
        }

        if(Cookie::has($this->cookieName)) {
            Cookie::delete($this->cookieName);
        }

        return true;
    }

    /**
     * @return bool
     *
     * Возвращает true, если пользователь - не авторизирвоан
     */

    protected function guest()
    {
        return is_null(Session::get($this->sessionName));
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
        if(!is_object(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance->$method(...$args);
    }
}