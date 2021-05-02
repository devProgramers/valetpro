<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ValetSignUp extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_USERNAME','no-reply@valetpro.com'), env('MAIL_FROM_NAME'))->subject("Valet Pro valet login details")->view('emails.valet-signup', ['email' => $this->data['email'],'password'=>$this->data['password'],'name'=>$this->data['name']]);
    }
}
