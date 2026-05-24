<?php

namespace App\Modules\Notifications\Mail;

use App\Modules\Bookings\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваше бронирование экскурсии подтверждено',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-paid',
        );
    }
}
