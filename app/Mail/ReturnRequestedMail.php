<?php

namespace App\Mail;

use App\Models\OrderReturn;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public OrderReturn $orderReturn;

    public function __construct(OrderReturn $orderReturn)
    {
        $this->orderReturn = $orderReturn;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Return Request Received — ' . $this->orderReturn->return_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.return-requested',
        );
    }
}
