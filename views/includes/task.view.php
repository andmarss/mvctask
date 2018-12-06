@if (isset($task))
    <li style="list-style-type: none;">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span>{{!is_null($task->user()) && $task->user()->name ? $task->user()->name : 'Пользователь'}}, <b>{{(new DateTime($task->created_at))->format('d-m-Y')}}</b></span>
                <span class="btn pull-right {{$task->completed ? 'btn-success' : 'btn-danger'}} btn-xs">{{$task->completed ? 'Закрыта' : 'Открыта'}}</span>

                @if(\App\Auth::check())

                    @if ((\App\Auth::id() === $task->user()->id) || \App\Auth::user()->admin)

                        @if(!$task->completed)
                            <a href="{{route('close', ['id' => $task->id])}}" class="btn btn-primary btn-xs pull-right" style="margin-right: 20px;">Закрыть</a>
                        @else
                            <a href="{{route('open', ['id' => $task->id])}}" class="btn btn-primary btn-xs pull-right" style="margin-right: 20px;">Открыть</a>
                        @endif

                    @endif

                @endif

                @if(\App\Auth::check())
                    @if ((\App\Auth::id() === $task->user()->id) || \App\Auth::user()->admin)
                        <a href="{{route('task.remove', ['id' => $task->id])}}" class="btn btn-default btn-xs pull-right" style="margin-right: 20px;">Удалить</a>
                    @endif
                @endif

            </div>
            <div class="panel-body">
                <div class="text-center">
                    <img src="{{asset($task->picture)}}" class="rounded image">
                </div>
                <h4 class="text-center">{{$task->title}}</h4>
                <p class="text-center">
                    {{$task->content}}
                </p>
            </div>
        </div>
    </li>
@endif
