<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $details['subject'] ?? config('app.name') }}</title>
</head>
<body>
    {{-- Callers pass either a ready-made "content" block or a "title"/"body" pair. --}}
    @if(!empty($details['content']))
        {!! $details['content'] !!}
    @else
        @if(!empty($details['title']))
            <h2>{{ $details['title'] }}</h2>
        @endif
        <p>{{ $details['body'] ?? '' }}</p>
    @endif
</body>
</html>
