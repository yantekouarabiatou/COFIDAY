<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user; // l'utilisateur créé
    public $roleName; // le nom du rôle assigné
    // Injection de l'utilisateur lors de la création du mail
    public function __construct(User $user, $roleName)
    {
        $this->user = $user;
        $this->roleName = $roleName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Création de votre compte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_created', // crée ce fichier dans resources/views/emails/user_created.blade.php
            with: [
                'user' => $this->user, // passer l'utilisateur à la vue
                'roleName' => $this->roleName, // passer le nom du rôle à la vue
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
        return [];
    }
}
