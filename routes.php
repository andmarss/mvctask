<?php

// GET запросы

$router->get('/', 'TasksController@index')->name('index');

$router->get('/register', 'AuthController@registerIndex')->name('register-index');

$router->get('/login', 'AuthController@loginIndex')->name('login-index');

$router->get('/personal-area/{id}', 'UsersController@personal_area')->name('personal-area');

$router->get('/delete-task/{id}', 'TasksController@remove')->name('task.remove');

$router->get('/results', 'TasksController@results')->name('results');

$router->get('/close-task/{id}', 'TasksController@close')->name('close');

$router->get('/open-task/{id}', 'TasksController@open')->name('open');

// POST запросы

$router->post('/register', 'AuthController@register')->name('register');

$router->post('/login', 'AuthController@login')->name('login');

$router->post('/logout', 'UsersController@logout')->name('logout');

$router->post('/store', 'TasksController@store')->name('store');

$router->post('/user-edit/{id}', 'UsersController@edit')->name('user.edit');