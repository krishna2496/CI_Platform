<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class AppMailer extends Mailable
{
    public $params;
    public $subject;
    public $from_email;
    public $from_name;
    public $template;
    public $data;

    /**
     * Create a new class instance.
     *
     * @param array $params
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        
        $this->data = isset($this->params['data']) ? $this->params['data'] : '';
        $this->subject = isset($this->params['subject']) ? $this->params['subject'] : '';
        
        $this->from_email = isset($this->params['from_email']) ? $this->params['from_email']
        : env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        
        $this->from_name = isset($this->params['from_name']) ? $this->params['from_name']
        : env('MAIL_FROM_NAME', 'Optimy');

        $this->template = $this->params['template'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->template)
        ->from($this->from_email, $this->from_name)
        ->with('data', $this->data);
    }
}
