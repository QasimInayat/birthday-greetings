@unless(config('mail.enabled'))
    <div class="alert alert-warning d-flex align-items-center">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <div>
            <strong>Outgoing email is switched off.</strong>
            Birthday greetings are being delivered by SMS only. To turn email back on,
            set <code>MAIL_ENABLED=true</code> in the <code>.env</code> file.
        </div>
    </div>
@endunless
