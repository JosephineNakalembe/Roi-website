<?php

namespace App\Mail;

use App\Models\CustomerMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class SupportReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public CustomerMessage $ticket;
    public array $allReplies;

    /**
     * Create a new message instance.
     */
    public function __construct(CustomerMessage $ticket)
    {
        $this->ticket = $ticket;
        $this->allReplies = $ticket->replies ?? [];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            subject: 'Re: ' . $this->ticket->subject,
        );

        return $envelope;
    }

    /**
     * Get the message headers for threading.
     */
    public function headers(): Headers
    {
        $messageId = 'support-' . $this->ticket->id . '@' . request()->getHost();

        $headers = new Headers(
            messageId: $messageId,
        );

        // If there are previous admin replies, use the first one as the parent for threading
        $firstAdminReplyId = 'support-' . $this->ticket->id . '-first@' . request()->getHost();
        $headers->references = [$firstAdminReplyId];
        $headers->inReplyTo = [$firstAdminReplyId];

        return $headers;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support-reply',
        );
    }
}