@php
    // Plain-text twin of emails.email_template. Mail without a text part is a
    // well-known spam signal, so every HTML message carries this alongside it.
    $source = !empty($details['content'])
        ? $details['content']
        : trim(($details['title'] ?? '') . "\n\n" . ($details['body'] ?? ''));

    // Turn block-level HTML into line breaks before stripping the rest.
    $text = preg_replace('/<(br|\/p|\/div|\/h[1-6]|\/tr)[^>]*>/i', "\n", $source);
    $text = preg_replace('/<[^>]+>/', '', $text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/[ \t]+/", ' ', $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
@endphp
{{ trim($text) }}
