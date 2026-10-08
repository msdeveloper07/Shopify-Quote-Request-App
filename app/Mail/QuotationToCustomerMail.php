<?php

namespace App\Mail;

use App\Models\Quote;
// use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotationToCustomerMail extends Mailable
{
    use SerializesModels;

    public $quote;
    public $customMessage;

    public function __construct(Quote $quote, $customMessage = null)
    {
        $this->quote = $quote;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        return $this->subject('Your Quote #' . $this->quote->id . ' from ' . config('app.name'))
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->replyTo(config('mail.from.address'), config('mail.from.name'))
                    ->view('emails.quotationtocustomer')
                    ->text('emails.quotationtocustomer_plain') // Add plain text version
                    ->with([
                        'quote' => $this->quote,
                        'customMessage' => $this->customMessage,
                    ]);
    }
}