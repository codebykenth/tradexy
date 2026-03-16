<?php

namespace App\Mail\Errors;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericJobFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $jobName;

    public $errorMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($jobName, $errorMessage)
    {
        $this->jobName = $jobName;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $environment = ucfirst(config('app.env'));

        return new Envelope(
            subject: "Failed Job: {$this->jobName} | Tradexy - $environment",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'errors.generic-job-failed',
            with: [
                'jobName' => $this->jobName,
                'errorMessage' => $this->errorMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
