@layout('layer/main')

@section('content')

    <div class="container">
        @if (\App\Session::has('success'))
            <div class="alert alert-success" role="alert">
                {{\App\Session::flash('success')}}
            </div>
        @endif

        @if (\App\Session::has('error'))
            <div class="alert alert-danger" role="alert">
                {{\App\Session::flash('error')}}
            </div>
        @endif

        @if(isset($query))
            <h3>{{$query}}</h3>
        @endif

        @forelse ($tasks as $task)
            @import('includes/task', ['task' => $task]);
        @empty
            <h4>Результатов не найдено</h4>
        @endforelse
    </div>

@endsection