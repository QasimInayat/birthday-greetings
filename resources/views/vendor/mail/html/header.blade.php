@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<<<<<<< HEAD
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
=======
@if ($logo = \App\Support\Branding::logoUrl())
<img src="{{ $logo }}" class="logo" alt="{{ config('app.name') }}" style="max-height: 64px; width: auto;">
>>>>>>> aa6fa8d09f730844716fb665631e25ad6e0ca434
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
