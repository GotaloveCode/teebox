<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $email_code;

    public function __construct($email_code)
    {
        $this->email_code = $email_code;
    }


    public function build()
    {
        return $this->subject(config('APP_NAME').' Email Verification')
            ->markdown('emails.verification');

    }
}
