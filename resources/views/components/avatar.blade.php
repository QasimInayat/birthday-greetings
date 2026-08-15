@props([
    'src'  => null,   // employee profile_image path, may be null or point at a deleted file
    'name' => '',
    'size' => 40,
])

@php
    $fallback = asset('assets/img/profile-default.png');

    // Not uploaded, or the row still points at a file that is no longer on disk.
    $hasImage = $src && file_exists(public_path($src));
@endphp

<img src="{{ $hasImage ? asset($src) : $fallback }}"
     data-fallback="{{ $fallback }}"
     onerror="this.onerror=null; this.src=this.dataset.fallback;"
     width="{{ $size }}"
     height="{{ $size }}"
     loading="lazy"
     alt="{{ $name ?: 'Profile photo' }}"
     {{ $attributes->merge(['class' => 'rounded-circle', 'style' => 'object-fit:cover']) }}>
