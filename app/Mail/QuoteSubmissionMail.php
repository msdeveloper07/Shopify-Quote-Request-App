<?php

namespace App\Mail;

// use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteSubmissionMail extends Mailable
{
    use SerializesModels;

    public $quoteData;
    public $shop;

    public function __construct($quoteData, $shop)
    {
        $this->quoteData = $quoteData;
        $this->shop = $shop;
    }

    public function build()
    {
        return $this->subject('New Quote Request from ' . $this->shop)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->replyTo($this->quoteData['email'], $this->quoteData['first_name'] . ' ' . $this->quoteData['last_name'])
                    ->view('emails.quote_submission')
                    ->text('emails.quote_submission')
                    ->with([
                        'quoteData' => $this->quoteData,
                        'shop' => $this->shop,
                    ]);
    }
}