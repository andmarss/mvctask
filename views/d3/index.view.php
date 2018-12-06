@layout('layer/main')

@section('content')
    <svg width="600" height="600">
        <rect x="300" y="100" fill="blue" width="100" height="200"></rect>
        <circle cx="200" cy="200" r="50" fill="pink" stroke="red" stroke-width="2"></circle>
        <line x1="100" y1="100" x2="120" y2="300" stroke="grey" stroke-width="2"></line>
    </svg>
@endsection