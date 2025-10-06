<?php

namespace App\Mail;

use App\Models\Contact;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;
    public $company;

    public function __construct(Contact $contact, Company $company)
    {
        $this->contact = $contact;
        $this->company = $company;
    }

    public function build()
    {
        return $this->subject('New Contact Request - VEXIM')
                    ->view('emails.contact-request-notification');
    }
}
