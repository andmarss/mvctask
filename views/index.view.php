@layout('layer/main')

@section('content')
    <div class="container">
        @if(\App\Session::has('success'))
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

        @if(\App\Auth::check())
            @import('add_task_form/index')
        @endif

        @if(count($tasks) > 0)
            @import('search/index')
        @endif

        @if(count($tasks) > 0)
            <ul class="list-group clearfix">
                @foreach($tasks as $task)
                    @import('includes/task', ['task' => $task])
                @endforeach
            </ul>
        @else
            <h3>Задач еще нет.
                @if(!\App\Auth::check())
                    <br><small class="text-muted"><a href="{{route('register-index')}}">Зарегестрируйтесь</a> или <a href="{{route('login-index')}}">авторизируйтесь</a>, что бы добавить свою задачу.</small>
                @endif
            </h3>
        @endif

        @if(count($tasks) > 0)
            {{paginate($per_page, $total)}}
        @endif
    </div>
@endsection

@section('scripts')
    <script src="{{asset('js/app.js')}}"></script>
    <script src="{{asset('js/main.js')}}"></script>
@endsection