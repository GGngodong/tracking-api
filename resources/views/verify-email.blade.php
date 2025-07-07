@component('mail::message')
    # Verify Your Email

    Click the button below to confirm your email address. This link will expire in a few hours.

    @component('mail::button', ['url' => $verificationLink])
        Verify Email
    @endcomponent

    If you did not create an account, no further action is required.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
