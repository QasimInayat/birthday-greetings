{{-- The template supplied a complete HTML document, so emit it untouched.
     Wrapping it would nest <html> inside <html>, which breaks some clients. --}}
{!! $details['content'] !!}
