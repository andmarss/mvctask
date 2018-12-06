<?php

/**
 * Class PostsController
 */

namespace App\Controllers;

use App\Auth;
use App\Controllers\Controller;
use App\Database\Task;
use App\Database\User;
use App\Database\DB;
use App\Session;
use App\UploadFile;

class TasksController extends Controller
{
    /**
     * @return mixed
     */

    public function index($request)
    {
        $per_page = 3;

        $total = Task::countRows();

        if (isset($request->page)) {
            $page = (int) $request->page;
            $from = $per_page * max(($page - 1), 0);
            $to = $per_page;
        } else {
            $from = 0;
            $to = $per_page;
        }

        $showTasks = Task::limit($from, $to, 'DESC')->get();

        return view('index', [
            'tasks' => $showTasks,
            'total' => $total,
            'per_page' => $per_page
        ]);
    }

    public function store($request)
    {
        $photo = $request->file('picture');

        [$width, $height] = $photo->size();
        $ext = $photo->extension();

        if(($width > 320 || $height > 240) && in_array($ext, ['jpeg', 'jpg', 'gif', 'png'])) {
            $pathWithFileName = $photo->resizeAndMove(['width' => 320, 'height' => 240], 'img/uploads/resized');

            $task = Task::create([
                'title' => $request->title,
                'slug' => slug($request->title),
                'content' => $request->content,
                'picture' => $pathWithFileName,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'user_id' => Auth::id()
            ]);
        } elseif ($width <= 320 && $height <= 240 && in_array($ext, ['jpeg', 'jpg', 'gif', 'png'])) {
            $name = time() . $photo->getClientOriginalName();

            $photo->move('img/uploads', $name);

            $task = Task::create([
                'title' => $request->title,
                'slug' => slug($request->title),
                'content' => $request->content,
                'picture' => 'img/uploads/' . $name,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'user_id' => Auth::id()
            ]);
        } else {
            if(!in_array($ext, ['jpeg', 'jpg', 'gif', 'png']) && ($width > 320 || $height > 240)) {
                Session::flash('error', 'Передано изображение неподдерживаемого типа ' . $ext . ' и не поддерживаемых размеров: ширина - ' . $width . 'px, высота - ' . $height . 'px. Максимальная ширина - 320px, максимальная высота - 240px');
            } elseif (in_array($ext, ['jpeg', 'jpg', 'gif', 'png']) && ($width > 320 || $height > 240)) {
                Session::flash('error', 'Передано изображение не поддерживаемых размеров: ширина - ' . $width . 'px, высота - ' . $height . 'px. Максимальная ширина - 320px, максимальная высота - 240px');
            } elseif (!in_array($ext, ['jpeg', 'jpg', 'gif', 'png']) && ($width <= 320 || $height <= 240)) {
                Session::flash('error', 'Передано изображение неподдерживаемого типа ' . $ext);
            }

            return redirect()->back();
        }

        Session::flash('success', 'Задача ' . $task->title . ' успешно добавлена');

        return redirect()->back();
    }

    public function remove($request, $id)
    {
        $task = Task::find($id);

        if ($task && ($task->user()->id === Auth::id()) || (Auth::user()->admin)) {
            $taskTitle = $task->title;

            $task->delete();

            Session::flash('success', 'Задача ' . $taskTitle . ' успешно удалена');

            return redirect()->back();
        } else {
            Session::flash('error', 'У вас недостаточно прав для удаления задачи, или произошла непредвиденная ошибка.');

            redirect()->back();
        }
    }

    public function results($request)
    {
        if(!isset($request->compeleted) && !isset($request->query)) {
            Session::flash('error', 'Хотя бы одно из полей запроса должно быть заполнено. Запрос не выполнен.');

            redirect()->back();
        }

        $tasks = null;
        $query = 'Результаты по запросу ';

        if( isset($request->completed) && isset($request->query) && $request->query) {

            $queryString = $request->query;

            if((int) $request->completed === 1) {
                $tasks = Task::whereIn('user_id', function ($query) use ($queryString) {

                    return $query->select('id')->from('users')->whereLike(['name' => $queryString])->orWhereLike(['email' => $queryString])->get();

                })->where(['completed' => 0])->orWhere(['completed' => 1])->orderBy('id')->desc()->get();

                $query .= '"Показывать": "Показывать все", ' . PHP_EOL;
            } elseif ((int) $request->completed === 2) {
                $tasks = Task::whereIn('user_id', function ($query) use ($queryString) {

                    return $query->select('id')->from('users')->whereLike(['name' => $queryString])->orWhereLike(['email' => $queryString])->get();

                })->where(['completed' => 0])->orderBy('id')->desc()->get();

                $query .= '"Показывать": "Показывать только не завершенные", ' . PHP_EOL;
            } else {
                $tasks = Task::whereIn('user_id', function ($query) use ($queryString) {

                    return $query->select('id')->from('users')->whereLike(['name' => $queryString])->orWhereLike(['email' => $queryString])->get();

                })->where(['completed' => 1])->orderBy('id')->desc()->get();

                $query .= '"Показывать": "Показывать только завершенные", ' . PHP_EOL;
            }

            $query .= '"Имя пользователя или email-адрес" : "' . $request->query . '"';

        } elseif (!isset($request->completed) &&  isset($request->query)) {

            $queryString = $request->query;

            $tasks = Task::whereIn('user_id', function ($query) use ($queryString) {
                return $query->select('id')->from('users')->whereLike(['name' => $queryString])->orWhereLike(['email' => $queryString])->get();
            })->orderBy('id')->desc()->get();

            $query .= '"Имя пользователя или email-адрес": "' . $request->query . '"';

        } elseif (isset($request->completed) &&  !$request->query) {
            $completed = null;

            if((int) $request->completed === 1) {
                $completed = [['completed' => 1], ['completed' => 0]];
                $query .= '"Показывать": "Показывать все"';
            } elseif ((int) $request->completed === 2) {
                $completed =  ['completed' => 0];
                $query .= '"Показывать": "Показывать только не завершенные"';
            } else {
                $completed = ['completed' => 1];
                $query .= '"Показывать": "Показывать только завершенные"';
            }

            if(count($completed) === 2) {
                $tasks = Task::where($completed[0])->orWhere($completed[1])->orderBy('id')->desc()->get();
            } elseif (count($completed) === 1) {
                $tasks = Task::where($completed)->orderBy('id')->desc()->get();
            }
        }

        return view('search/results', [
            'tasks' => $tasks,
            'query' => $query
        ]);
    }

    public function close($request, $id)
    {
        $task = Task::find($id);

        if((Auth::check() && Auth::id() === $task->user()->id || Auth::user()->admin) && !$task->completed) {
            $task->completed = 1;
            $task->save();

            Session::flash('success', 'Задача ' . $task->name . ' успешно завершена.');
        } else {
            Session::flash('error', 'Задача уже завершена, или у вас недостаточно прав для её изминения');
        }

        return redirect()->back();
    }

    public function open($request, $id)
    {
        $task = Task::find($id);

        if((Auth::check() && Auth::id() === $task->user()->id || Auth::user()->admin) && $task->completed) {
            $task->completed = 0;
            $task->save();

            Session::flash('success', 'Задача ' . $task->name . ' успешно открыта.');
        } else {
            Session::flash('error', 'Задача уже открыта, или у вас недостаточно прав для её изминения');
        }

        return redirect()->back();
    }
}
