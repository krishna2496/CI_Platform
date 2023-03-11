<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use App\Mail\AppMailer;
use Illuminate\Support\Facades\Log;

class EmailHelper
{
    /**
     * Send email
     *
     * @param  array $params
     * @return boolean
     */
    public function sendEmail(array $params)
    {
        Mail::to($params['to'])->send(new AppMailer($params));
        Log::info('Mail sent to '. $params['to']. 'with subject :' . $params['subject']);
        return true;
    }
}
