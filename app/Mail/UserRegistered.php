<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $loginId;
    public $password;
    public $loginUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($loginId, $password, $loginUrl)
    {
        $this->loginId = $loginId;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.user_registered')
                    ->subject('ユーザー登録完了のお知らせ')
                    ->with([
                        'loginId' => $this->loginId,
                        'password' => $this->password,
                        'loginUrl' => $this->loginUrl,
                    ]);
    }
}
