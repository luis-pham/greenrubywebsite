<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $data;
     
    /**
     * It will create new instance message.
     *
     * @return void
     */
    public function __construct($subject, $data)
    {  
        $this->subject = $subject;
        $this->data = $data;
    }
     
    /**
     * It is used to build the message.
     *
     * @return $this
     */
    public function build()
    {  
        $language = AdLanguageService::getDefaultLanguage();
        $config = Utilities::getAllConfig($language);
        $name = array_key_exists('website-name', $config) && $config['website-name'] ? $config['website-name'] : null;
        return $this->from(config('mail.from.address'), $name)->subject($this->subject)->markdown('email.template')->with('data', $this->data);
    }
}
