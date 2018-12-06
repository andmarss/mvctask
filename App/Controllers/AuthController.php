<?php
/**
 * Created by PhpStorm.
 * User: Zver
 * Date: 25.11.2018
 * Time: 20:52
 */

namespace App\Controllers;

use App\Auth;
use App\Controllers\Controller;
use App\Hash;

class AuthController extends Controller
{
    public function registerIndex()
    {
        return view('register/index');
    }

    public function register($request)
    {
        $this->validate($request, [
            'name' => [
                'required' => true,
                'unique' => 'users'
            ],
            'email' => [
                'required' => true,
                'unique' => 'users'
            ],
            'password' => [
                'required' => true
            ]
        ]);

        if(Auth::attempt($request)) {
            return redirect()->back(['errors' => 'Пользователь с таким Email\'ом уже существует']);
        } else {
            $user = Auth::register($request);

            if($user) {
                return redirect('/personal-area/' . $user->id);
            }
        }
    }

    public function loginIndex()
    {
        return view('login/index');
    }

    public function login($request)
    {
        $this->validate($request, [
            'email' => [
                'required' => true
            ],
            'password' => [
                'required' => true
            ]
        ]);

        $remember = false;

        if(isset($request->remember)) {
            $remember = true;
        }

        if($user = Auth::login($request, $remember)) {
            return redirect('/personal-area/' . $user->id);
        } else {
            return redirect()->back(['errors' => 'Введены некорректные данные']);
        }
    }
}