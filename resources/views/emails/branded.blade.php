{{-- Laravel's built-in branded mail layout: header, panel, footer. --}}
<x-mail::message>
@isset($title)
# {{ $title }}
@endisset

{!! $body !!}

@isset($actionUrl)
<x-mail::button :url="$actionUrl">
{{ $actionText ?? 'View' }}
</x-mail::button>
@endisset
</x-mail::message>
