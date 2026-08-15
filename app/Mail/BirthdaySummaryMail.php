<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BirthdaySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $todaysBirthdays;
    public $upcomingBirthdays;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($todaysBirthdays, $upcomingBirthdays)
    {
        $this->todaysBirthdays = $todaysBirthdays;
        $this->upcomingBirthdays = $upcomingBirthdays;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Birthday Summary Report - ' . date('d M Y'))
                    ->view('emails.birthday_summary');
    }
}
