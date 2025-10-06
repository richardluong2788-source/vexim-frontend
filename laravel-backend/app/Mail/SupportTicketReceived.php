<?php

namespace App\Mail;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupportTicketReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;
    public $user;

    public function __construct(SupportTicket $ticket, User $user)
    {
        $this->ticket = $ticket;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Support Ticket Received - #' . $this->ticket->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-ticket-received',
            with: [
                'ticketId' => $this->ticket->id,
                'subject' => $this->ticket->subject,
                'message' => $this->ticket->message,
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'createdAt' => $this->ticket->created_at->format('M d, Y H:i'),
            ]
        );
    }
}
