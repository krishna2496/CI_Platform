<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Events\User\UserNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $from_email;
    public $from_name;
    public $template;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $template, array $data)
    {
        $this->data = $data;
        $this->template = $template;
        $this->subject = $data['subject'];
        
        $this->from_email = isset($this->data['from_email']) ? $this->data['from_email']
        : env('MAIL_FROM_ADDRESS', 'noreply@example.com');
            
        $this->from_name = isset($this->data['from_name']) ? $this->data['from_name']
        : env('MAIL_FROM_NAME', 'Optimy');
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
