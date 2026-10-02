<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;
    public $recipientType; // user ya provider

    public function __construct($booking, $recipientType)
    {
        $this->booking = $booking;
        $this->recipientType = $recipientType;
    }

    public function build()
    {
        return $this->subject('Booking Confirmation')
            ->view('emails.booking-created')
            ->with([
                'booking' => $this->booking,
                'recipientType' => $this->recipientType,
            ]);
    }
}
