<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use function collect;

class TranscriptRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public string $body;
    public string $subjectText;
    public array $files;

    public function __construct(string $subjectText, string $body, array $files = [])
    {
        $this->subjectText = $subjectText;
        $this->body = $body;
        $this->files = $files;
    }
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
          return new Envelope(
            subject: $this->subjectText
        ); 
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
          return new Content(
            view: 'email.transcript',
            with: [
                'body' => $this->body,
            ],
        );
    }
//    
//    public function content(): Content
//    {
//        return new Content(
//            markdown: 'email.transcript'
//        );
//    }

    
    
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
         return collect($this->files)->map(function ($file) {
            return Attachment::fromPath($file->getRealPath())
                ->as($file->getClientOriginalName())
                ->withMime($file->getMimeType());
            })->toArray();
    }
}
