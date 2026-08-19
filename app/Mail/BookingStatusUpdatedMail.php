<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $role;
    public $oldStatus;
    public $newStatus;

    public function __construct(Booking $booking, string $role, string $oldStatus, string $newStatus)
    {
        $this->booking   = $booking;
        $this->role      = $role;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function build()
    {
        return $this->subject("Booking Status Updated: {$this->newStatus}")
            ->view('emails.booking_status') // ✅ ab plain blade file use karega
            ->with([
                'booking'       => $this->booking,
                'recipientType' => $this->role,
                'oldStatus'     => $this->oldStatus,
                'newStatus'     => $this->newStatus,
            ]);
    }
}
