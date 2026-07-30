<?php

namespace App\Modules\Notifications\Mail;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Support\BookingPriceCalculator;
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
            subject: 'Предоплата внесена — бронирование экскурсии подтверждено',
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing('excursion');
        $price = app(BookingPriceCalculator::class)->calculate(
            $this->booking->excursion,
            $this->booking->adult_tickets_count,
            $this->booking->child_tickets_count,
        );

        return new Content(
            view: 'emails.booking-paid',
            with: ['price' => $price],
        );
    }
}
