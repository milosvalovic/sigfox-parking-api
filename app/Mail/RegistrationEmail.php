<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $registrationObject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($registrationObject)
    {
        $this->registrationObject = $registrationObject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email_verification')->subject('Sigfox Parking - Registrácia')->from('no_reply@sigfox-pakring.sk');
    }
}
