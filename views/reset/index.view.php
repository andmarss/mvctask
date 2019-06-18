@layout('layer/main')

@section('content')

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

    <div class="container">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Восстановление пароля</div>
                <div class="panel-body">
                    <form role="form" method="POST" enctype="multipart/form-data" action="{{route('reset-password')}}" class="form-horizontal">
                        <input type="hidden" name="token" value="{{csrf_token()}}">

                        <div class="form-group">
                            <label for="email" class="col-md-4 control-label">Email адрес</label>
                            <div class="col-md-6">
                                <input id="email" type="email" name="email" value="{{old('email')}}" required="required" autofocus="autofocus" class="form-control">
                                @if (isset($errors) && count($errors) > 0 && isset($errors->email))
                                    @foreach ($errors->email as $error)
                                        <p class="text-danger">{{$error}}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password" class="col-md-4 control-label">Пароль</label>
                            <div class="col-md-6">
                                <input id="password" type="password" name="password" required="required" class="form-control" value="{{old('password')}}">
                                @if (isset($errors) && count($errors) > 0 && isset($errors->password))
                                    @foreach ($errors->password as $error)
                                        <p class="text-danger">{{$error}}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password" class="col-md-4 control-label">Пароль еще раз</label>
                            <div class="col-md-6">
                                <input type="password" name="password_confirm" required="required" class="form-control" value="{{old('password_confirm')}}">
                                @if (isset($errors) && count($errors) > 0 && isset($errors->password_confirm))
                                    @foreach ($errors->password_confirm as $error)
                                        <p class="text-danger">{{$error}}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Восстановить доступ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection