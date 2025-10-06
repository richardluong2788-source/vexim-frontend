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

class SupportTicketReply extends Mailable implements ShouldQueue
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
            subject: 'Response to Your Support Ticket - #' . $this->ticket->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support-ticket-reply',
            with: [
                'ticketId' => $this->ticket->id,
                'subject' => $this->ticket->subject,
                'originalMessage' => $this->ticket->message,
                'adminReply' => $this->ticket->admin_reply,
                'userName' => $this->user->name,
                'status' => $this->ticket->status,
                'repliedAt' => $this->ticket->replied_at->format('M d, Y H:i'),
            ]
        );
    }
}
