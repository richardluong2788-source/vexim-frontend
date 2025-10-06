<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactResponse extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;
    public $shareFullContact;

    public function __construct(Contact $contact, $shareFullContact)
    {
        $this->contact = $contact;
        $this->shareFullContact = $shareFullContact;
    }

    public function build()
    {
        return $this->subject('Supplier Response - VEXIM')
                    ->view('emails.contact-response');
    }
}
