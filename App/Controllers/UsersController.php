<?php

/**
 * Class UsersController
 */

namespace App\Controllers;

use App\Database\User;
use App\Auth;
use App\Session;

class UsersController extends Controller
{
    /**
     * @return mixed
     */

    public function index()
    {
        $users = User::all();

        return view('users', [
            'users' => $users
        ]);
    }

    public function personal_area($request, $id = null)
    {
        $user = null;

        if($id) {
            $user = User::find($id);
        }

        if($user) {
            return view('personal_area/index', [
                'user' => $user
            ]);
        } else {
            Session::flash('error', 'Пользователь не найден, или произошла ошибка при выполнении запроса');

            return redirect()->back();
        }
    }

    public function logout()
    {
        if(Auth::check()) {
            Auth::logout();
        }

        return redirect('/');
    }

    public function edit($request, $id)
    {
        $user = User::find($id);

        if(Auth::check() && $user && Auth::user()->id === $user->id) {
            $this->validate($request, [
                'name' => [
                    'required' => true
                ],
                'email' => [
                    'required' => true,
                    'email' => true
                ],
                'phone' => [
                    'int' => true,
                    'min' => 6,
                    'max' => 15
                ]
            ]);

            if(isset($request->name)) {
                $user->name = $request->name;
            }

            if(isset($request->password)) {
                $user->password = password_hash($request->password, PASSWORD_BCRYPT);
            }

            if (isset($request->email)) {
                $user->email = $request->email;
            }

            if(isset($request->phone)) {
                $user->phone = $request->phone;
            }

            $user->save();

            Session::flash('success', 'Ваши даныне успешно изменены');

            return redirect()->back();
        } else {
            Session::flash('error', 'У вас недостаточно прав для данного действия');

            return redirect()->back();
        }
    }
}