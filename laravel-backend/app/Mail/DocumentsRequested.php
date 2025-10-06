<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentsRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $message;
    public $requiredDocuments;

    public function __construct(Company $company, $message, $requiredDocuments)
    {
        $this->company = $company;
        $this->message = $message;
        $this->requiredDocuments = $requiredDocuments;
    }

    public function build()
    {
        return $this->subject('Additional Documents Required - VEXIM')
                    ->view('emails.documents-requested');
    }
}
