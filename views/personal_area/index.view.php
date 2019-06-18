@layout('layer/main')

@section('content')
    <div class="container">
        @if (\App\Auth::check() && (\App\Auth::id() === $user->id || \App\Auth::user()->admin))

            @if (\App\Session::has('success'))
                <div class="alert alert-success" role="alert">
                    {{\App\Session::flash('success')}}
                </div>
            @endif

                @if(\App\Session::has('error'))

                    @if($errors = \App\Session::flash('error'))

                        <div class="container">

                            <div class="alert alert-danger" role="alert">

                                @if(isset($errors) && is_string($errors))
                                    <p>{{$errors}}</p>
                                @endif

                                @if(isset($errors) && count($errors) > 0)

                                    @if(!is_object($errors) && is_array($errors))

                                        @foreach ($errors as $error)

                                            @if(is_array($error))

                                                @foreach ($error as $err)

                                                    <p>{{$err}}</p>

                                                @endforeach

                                            @elseif(is_string($error))

                                                <p>{{$error}}</p>

                                            @endif

                                        @endforeach

                                    @endif

                                @endif

                            </div>

                        </div>

                    @endif

                @endif

            @if (isset($user->name))
                <h1>Привет, {{$user->name}}</h1>
            @else
                <h1>Привет, пользователь!</h1>
            @endif

            @import('personal_area/edit', ['user' => $user])

            @if(count($tasks) > 0)
                <h3>Список ваших задач</h3>

                @import('personal_area/table', ['tasks' => $tasks])
            @endif

        @else
            <h3>У вас нет прав для просмотра данной страницы.</h3>
        @endif
    </div>
@endsection