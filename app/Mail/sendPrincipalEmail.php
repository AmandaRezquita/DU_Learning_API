<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendPrincipalEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $username;
    public $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Principal Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        $content = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>School Account Created</title>
            </head>
            <body>
                <p>Dear School Admin,</p>
                <p>Your account has been created successfully. Here are your login details:</p>
                <p><strong>Username:</strong> {$this->username}</p>
                <p><strong>Password:</strong> {$this->password}</p>
                <p>Please log in and change your password after the first login.</p>
                <p>Thank you</p>
            </body>
            </html>
        ";

        return $this->subject('Welcome to the School Platform')
                    ->html($content); 
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
