<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string */
    public $verificationLink;

    public function __construct(string $link)
    {
        $this->verificationLink = $link;
    }

    public function build()
    {
        return $this
            ->subject('Please verify your email')
            ->markdown('emails.verify-email')   // points to resources/views/emails/verify-email.blade.php
            ->with(['verificationLink' => $this->verificationLink]);
    }
}
