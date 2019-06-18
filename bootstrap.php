<?php

session_start();

use App\{App, Collect, Pagination};
use App\Database\DB;
use App\Controllers\{Request, Router};

/**
 * служебные переменные
 */

App::bind('config', require 'config.php');

App::bind('database', ( new DB( App::get('config/database') ) ) );

App::bind('DEV', true);

/**
 * авторизация по кукам, если пользователь выбирал "Запомнить меня" при авторизациив
 */

if (\App\Cookie::has(App::get('config/remember/cookie_name')) && !\App\Session::has(App::get('config/session/session_name'))) {

    $hash = \App\Cookie::get(App::get('config/remember/cookie_name'));

    $hashCheck = DB::table('users_session')
        ->where(['hash'=>$hash])
        ->orderBy('id')
        ->desc()
        ->first('UserSession');

    if($hashCheck) {
        $user = \App\Database\User::find($hashCheck->user_id);

        if($user) {
            \App\Auth::login([
                'email' => $user->email,
                'password' => $user->password
            ]);

            \App\Session::flash('success', 'Вы успешно авторизированы');
        }
    }
}

/**
 * @param $name
 * @param array $data
 * @return \App\View
 *
 * Отображает шаблон с переданными переменными
 */

function view($name, $data = [])
{
    if(strpos($name, '.view.php')){
        return ( new \App\View("views/{$name}", $data) );
    } else {
        return ( new \App\View("views/{$name}.view.php", $data) );
    }
}

/**
 * @param string $path
 * @param array $data
 * @return \App\Redirect
 *
 * Редиректит пользователя по указанному маршруту
 */

function redirect($path = '', $data = [])
{
    return (new \App\Redirect($path, $data));
}

/**
 * @param array $collection
 * @return Collect
 *
 * Возвращает объект коллекции
 */

function collect(array $collection = []) {
    return new Collect($collection);
}

/**
 * @param string $str
 * @return string
 *
 * Преобразует кирилические буквы в латинские
 */

function slug(string $str = '') {
    if (isset($str)) {
        $cyrillic = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];

        $latin = [
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
        ];

        return strtolower(preg_replace('/[\s]+/', '-', str_replace($cyrillic, $latin, $str)));
    }
}

/**
 * @return string
 *
 * возвращает доменное имя приложения, включая протокол
 */

function domain()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];

    return $protocol . $domainName;
}

/**
 * @param $per_page
 * @param $total
 * @return string
 *
 * Отображает пагинацию по переданным свойствам
 */

function paginate($per_page, $total)
{
    return (new Pagination())->setPerPage($per_page)->setTotal($total)->html();
}

/**
 * @return Request
 *
 * Функция для работы с объектом запроса
 */

function request()
{
    return (new Request())->{Request::method()}();
}

/**
 * @param $path
 * @return string
 *
 * Функция для работы с загружаемыми данными
 */

function asset($path)
{
    return domain(). '/content/' . $path;
}

/**
 * @param $name
 * @param array $data
 *
 * Импортит шаблон по имени
 */

function import($name, $data = []) {
    echo view($name, $data);
}

/**
 * @return mixed|null
 *
 * Генерирует csrf-токен
 */

function csrf_token() {
    return \App\Token::generate();
}

/**
 * @return string
 *
 * Вовзаращает скрытое поле, которое можно добавить на форму запроса
 */

function csrf_field() {
    return "<input type=\"hidden\" name=\"token\" value=\"" . csrf_token() . "\" />";
}

/**
 * @param $name
 * @param array $data
 * @return string
 *
 * Получает шаблон маршрута
 * Возвращает оригинальную строку маршрута с подставленными данными
 */

function route($name, $data = []){
    return domain() . Router::convertUri($name, $data);
}

/**
 * @param $name
 * @return string
 *
 * Служит для подстановки значений,
 * если вдруг по случайности пользователь после запроса вернулся обратно на страницу
 */

function old($name)
{
    if(\App\Session::has('old')) {
        $request = \App\Session::flash('old');

        if(isset($request->{$name})) {
            return $request->{$name};
        } else {
            return '';
        }
    } else {
        return '';
    }
}

/**
 * @param $data
 * Служебная функция для отображения данных на странице
 */

function dd($data) {
    echo "<pre>";
    var_dump($data);
    echo "</pre>"; die;
}