<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $reason;

    public function __construct(Company $company, $reason)
    {
        $this->company = $company;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Verification Status - VEXIM')
                    ->view('emails.verification-rejected');
    }
}
