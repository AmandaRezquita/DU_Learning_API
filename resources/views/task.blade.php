<div class="container">
    <h2>Tasks for Class: {{ $class_id }} - Subject: {{ $subject_id }}</h2>

    @foreach ($tasks as $task)
        <div class="task">
            <h4>{{ $task->title }}</h4>
            <p>{{ $task->description }}</p>
            @if ($task->file)
                <a href="https://docs.google.com/gview?url={{ urlencode(asset('storage/file/' . $task->file)) }}&embedded=true"
                    target="_blank">View File</a>
            @endif


            @if ($task->link)
                <a href="{{ $task->link }}" target="_blank">View Link</a>
            @endif
        </div>
    @endforeach
</div>