<?php

namespace App\Mail;

use App\Models\CustomerMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketOpenedAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public CustomerMessage $ticket;

    public function __construct(CustomerMessage $ticket)
    {
        $this->ticket = $ticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Support Ticket — ' . $this->ticket->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-opened-admin',
        );
    }
}