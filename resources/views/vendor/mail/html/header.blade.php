@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logo = \App\Support\Branding::logoUrl())
<img src="{{ $logo }}" class="logo" alt="{{ config('app.name') }}" style="max-height: 64px; width: auto;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
