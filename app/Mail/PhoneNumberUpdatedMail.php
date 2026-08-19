<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhoneNumberUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $oldPhone;
    public $newPhone;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $oldPhone, $newPhone)
    {
        $this->user = $user;
        $this->oldPhone = $oldPhone;
        $this->newPhone = $newPhone;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your phone number has been updated')
                    ->view('emails.phone_updated');
    }
}
