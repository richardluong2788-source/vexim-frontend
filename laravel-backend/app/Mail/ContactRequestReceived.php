<?php

namespace App\Mail;

use App\Models\Contact;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactRequestReceived extends Mailable implements ShouldQueue
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
        return $this->subject('Contact Request Received - VEXIM')
                    ->view('emails.contact-request-received');
    }
}
