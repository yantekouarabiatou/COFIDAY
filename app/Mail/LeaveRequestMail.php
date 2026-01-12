<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Conge as LeaveRequest;

class LeaveRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public LeaveRequest $leave;
    public string $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(LeaveRequest $leave, string $pdfPath)
    {
        //
        $this->leave = $leave;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle demande de congé'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.leave_request',
            with: [
                'leave' => $this->leave,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Demande_conge_' . $this->leave->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
