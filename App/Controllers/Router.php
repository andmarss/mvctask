<?php

/**
 * Class Router
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Controllers\Request;
use App\Token;
use App\App;

class Router
{
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];

    protected static $names = [];

    protected $request = [];

    protected $uri = '';

    protected $requestInstance;

    protected static $instance;

    protected $verifyCsrfMiddleware;

    protected $authMiddleware;

    protected $middlewares = [];

    public function __construct()
    {
        $this->verifyCsrfMiddleware = new \Middleware\VerifyCsrfToken();
        $this->authMiddleware = new \Middleware\AuthMiddleware();
    }

    /**
     * @param $file
     * @return static
     *
     * Загружает файл маршрутов
     */
    public static function load($file)
    {
        $router = new static();

        require_once $file;

        return $router;
    }

    /**
     * @param $routes
     *
     * Записывает все маршруты
     */
    public function define($routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param $uri
     * @param $controller
     * @return $this
     *
     * Записывает uri GET-запросов, и привязывает их к контроллерам
     */

    protected function get($uri, $controller)
    {
        $match = $this->matchUriIsPattern($uri);

        $this->uri = $uri;

        if($match) {
            if(!isset($this->routes['GET']['patterns'])) {
                $this->routes['GET']['patterns'] = [];
            }

            $this->routes['GET']['patterns'][] = $uri;
        }

        $this->routes['GET'][$uri] = $controller;

        return $this;
    }

    /**
     * @param $uri
     * @param $controller
     * @return $this
     *
     * Записывает uri POST-запросов, и привязывает их к контроллерам
     */

    protected function post($uri, $controller)
    {
        $match = $this->matchUriIsPattern($uri);

        $this->uri = $uri;

        if($match) {
            if(!isset($this->routes['POST']['patterns'])) {
                $this->routes['POST']['patterns'] = [];
            }

            $this->routes['POST']['patterns'][] = $uri;
        }

        $this->routes['POST'][$uri] = $controller;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     *
     * Привязывает uri к соответствующему middleware
     */

    protected function middleware($name)
    {
        switch ($name) {
            case 'auth':
                if(!is_array($this->middlewares[$this->uri])) {
                    $this->middlewares[$this->uri] = [];
                }

                $this->middlewares[$this->uri][] = function (){
                    $this->authMiddleware->handle();
                };
                break;
        }

        return $this;
    }

    /**
     * @param $uri
     * @return mixed
     * @throws \Exception
     *
     * Обрабатывает запросы
     */

    public function direct($uri, $method)
    {
        $uri = ($uri === '') ? '/' : '/' . $uri;

        [$parameters, $pattern] = $this->inPatternsRoutesExists($uri, $method);

        // Проверяем, подписан ли маршрут к middleware
        // Если да - вызываем его
        if(array_key_exists($uri, $this->middlewares)) {
            foreach($this->middlewares[$uri] as $middleware) {
                $middleware();
            }
        } elseif ($parameters && array_key_exists($pattern, $this->middlewares)) {
            foreach($this->middlewares[$pattern] as $middleware) {
                $middleware();
            }
        }

        if(is_callable($this->routes[$method][$uri])) {

            $method = $this->routes[$method][$uri];

            return $this->call(function ($request, ...$params) use ($method) { return $method($request, ...$params); });

        }  elseif($parameters && is_callable ($this->routes[$method][$pattern])) {

            $method = $this->routes[$method][$pattern];

            if($parameters) {
                return $this->call(function ($request, ...$params) use ($method) { return $method($request, ...$params); }, ...$parameters);
            } else {
                return $this->call(function ($request, ...$params) use ($method) { return $method($request, ...$params); });
            }

        } else {
            if($parameters) {
                return $this->callAction(
                    ...array_merge(explode('@', $this->routes[$method][$pattern]), ...$parameters)
                );
            } else {
                if(!is_callable($method) && array_key_exists($uri, $this->routes[$method]) || array_key_exists('/' . $uri, $this->routes[$method])){
                    return $this->callAction(
                        ...explode('@', $this->routes[$method][$uri])
                    );
                }
            }
        }

        throw new \Exception('Для этого URI не указан маршрут.');
    }

    /**
     * @param $controller
     * @param $action
     * @param array ...$params
     * @return $this
     * @throws \Exception
     *
     * Вызывает метод контроллера, к которому было привязано uri
     *
     * Если метод не найден - выбрасывает исключение
     */

    protected function callAction($controller, $action, ...$params)
    {
        $controller = "App\\Controllers\\{$controller}";
        $controller = new $controller;

        if(!method_exists($controller, $action)){
            throw new \Exception(
                "Экшн $action отсутствует в контроллере $controller"
            );
        }

        return $this->call(function ($request, ...$params) use ($controller, $action) { return $controller->{$action}($request, ...$params); }, ...$params);
    }

    /**
     * @param $function
     * @param array ...$params
     * @return $this
     *
     * Вызывает переданную функцию
     */

    private function call($function, ...$params)
    {
        if(Request::method() === 'GET'){
            echo $function(Request::get(), ...$params);
            return $this;
        } elseif (Request::method() === 'POST'){

            $request = Request::post();

            $this->verifyCsrfMiddleware->handle($request);

            echo $function($request, ...$params);

            return $this;
        }

        return $this;
    }

    /**
     * @param $uri
     * @param $method
     * @return array|bool
     *
     * Проверяет, есть ли подходящий шаблон для uri
     */

    protected function inPatternsRoutesExists($uri, $method)
    {
        if(array_key_exists('patterns', $this->routes[$method])) {
            $patterns = $this->routes[$method]['patterns'];

            foreach ($patterns as $pattern) {
                $result = $this->match($pattern, $uri);
                if($result) {
                    return [$result, $pattern];
                } else {
                    continue;
                }

                return false;
            }
        } else {
            return false;
        };
    }

    /**
     * @param $name
     * @return $this
     * @throws \Exception
     *
     * Привязывает маршрут к уникальному алиасу
     */

    protected function name($name)
    {
        if(array_key_exists($name, static::$names)) {
            if(App::get('DEV')) {
                throw new \Exception("Имя маршрута \"$name\" уже объявлено. Выберите другое имя.");
            } else {
                throw new \Exception('Упс... Что-то пошло не так... Сообщите о проблеме администратору. Код: 1500');
            }
        }

        static::$names[$name] = $this->uri;

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     *
     * Вызывает маршрут по алиасу
     */

    protected function callRouteByName($name)
    {
        if(array_key_exists($name, static::$names)) {
            return $this->direct( static::$names[$name], Request::method() );
        } else {
            if(App::get('DEV')) {
                throw new \Exception("Имя маршрута \"$name\" не объявлено. Сперва нужно его объявить.");
            } else {
                throw new \Exception('Упс... Что-то пошло не так... Сообщите о проблеме администратору. Код: 1500');
            }
        }
    }

    /**
     * @param $name
     * @param array $data
     * @return mixed|string
     * @throws \Exception
     *
     * Конвертирует алиас в url-адрес, к которому алиас привязан
     */

    protected function convertUri($name, $data = [])
    {
        if(array_key_exists($name, static::$names)) {

            $uri = static::$names[$name];
            $isPattern = $this->matchUriIsPattern($uri);

            if ($isPattern) {
                preg_match_all('/\{([^\{|\}]+)\}/', $uri, $m);

                if (count($m[0]) > 0 && (count($m[0]) === count($data))) {

                    $uri = preg_replace('/\{[^\{\}]+\}/', '%s', $uri);

                    $route = sprintf($uri, ...array_values($data));

                    return $route;

                } else {
                    if(App::get('DEV')) {
                        throw new \Exception("Паттерн маршрута не совпадает с переданными свойствами");
                    } else {
                        throw new \Exception('Упс... Что-то пошло не так... Сообщите о проблеме администратору. Код: 1600');
                    }
                }
            } else {
                return $uri;
            }

        } else {
            if(App::get('DEV')) {
                throw new \Exception("Имя маршрута не объявлено.");
            } else {
                throw new \Exception('Упс... Что-то пошло не так... Сообщите о проблеме администратору. Код: 1700');
            }
        }


    }

    /**
     * @param $uri
     * @return bool
     *
     * Проверяет, есть ли у uri паттерн, по которому этот uri должен отработать
     */

    protected function matchUriIsPattern($uri)
    {
        preg_match('/\{([^\{|\}]+)\}/', $uri, $m);

        return count($m) > 0;
    }

    /**
     * @param $pattern
     * @param $uri
     * @return array|bool
     *
     * Превращает паттер в uri
     * Если паттер и uri не совпадают - возвращает false
     */

    protected function match($pattern, $uri)
    {
        $pattern = preg_replace('/\{[^\{\}]+\}/', '(.+)', $pattern); // убираем фигурные скобки, заменяем их круглыми
        $pattern = trim($pattern, '/'); // убираем по бокам слеши
        $pattern = preg_replace('/\/+/', '\/', $pattern); // а так же все лишние слеши

        preg_match_all('/' . $pattern . '/', $uri, $m); // применяем паттерн, получаем id, который был передан в маршрут

        if($m && $m[0]) {
            return array_slice($m, 1);
        } else {
            return false;
        }
    }

    /**
     * @param string $uri
     * @return bool
     *
     * Проверяет, есть ли на текущей странице переданный фрагмент url
     */

    protected function has($uri = '')
    {
        $uri = trim( quotemeta($uri), '/' );

        preg_match("/$uri/", trim( $_SERVER['REQUEST_URI'], '/' ), $m);

        return !!$m;
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
