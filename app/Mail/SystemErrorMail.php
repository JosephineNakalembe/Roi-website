<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SystemErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $errorClass;
    public string $errorMessage;
    public string $errorFile;
    public string $errorLine;
    public string $url;
    public string $occurredAt;

    public function __construct(Throwable $e, string $url)
    {
        $this->errorClass = get_class($e);
        $this->errorMessage = $e->getMessage();
        $this->errorFile = $e->getFile();
        $this->errorLine = (string) $e->getLine();
        $this->url = $url;
        $this->occurredAt = now()->toDateTimeString();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Server error — ' . class_basename($this->errorClass),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system-error',
        );
    }
}
