<?php

// GET запросы

$router->get('/', 'TasksController@index')->name('index')->middleware('auth');

$router->get('/register', 'AuthController@registerIndex')->name('register-index');

$router->get('/login', 'AuthController@loginIndex')->name('login-index');

$router->get('/personal-area/{id}', 'UsersController@personal_area')->name('personal-area')->middleware('auth');

$router->get('/delete-task/{id}', 'TasksController@remove')->name('task.remove')->middleware('auth');

$router->get('/results', 'TasksController@results')->name('results')->middleware('auth');

$router->get('/close-task/{id}', 'TasksController@close')->name('close')->middleware('auth');

$router->get('/open-task/{id}', 'TasksController@open')->name('open')->middleware('auth');

$router->get('/reset-password', function (){

    return view('reset/index');

})->name('reset-index');

// POST запросы

$router->post('/register', 'AuthController@register')->name('register');

$router->post('/login', 'AuthController@login')->name('login');

$router->post('/logout', 'UsersController@logout')->name('logout');

$router->post('/store', 'TasksController@store')->name('store')->middleware('auth');

$router->post('/user-edit/{id}', 'UsersController@edit')->name('user.edit')->middleware('auth');

$router->post('/reset-password', function ($request){

    if(!$request->email && !$request->password && !$request->password_confirm) {
        \App\Session::flash('error', 'Заполнены не все поля');

        return redirect()->back();
    } elseif ($request->password !== $request->password_confirm) {
        \App\Session::flash('error', 'Поля пароля и подтверждения пароля не совпадают');

        return redirect()->back();
    }

    $user = \App\Database\User::where(['email' => $request->email])->first();

    if($user) {
        $user->password = password_hash($request->password, PASSWORD_BCRYPT);

        $user->save();

        \App\Session::flash('success', 'Пароль успешно обновлен');

        return redirect()->route('login-index');
    } else {
        \App\Session::flash('error', 'Ошибка запроса. Повторите попытку позже');

        return redirect()->back();
    }

})->name('reset-password');