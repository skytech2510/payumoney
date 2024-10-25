<?php
namespace App\libraries;

use Mail;

class MailService
{

    public function send(array $data, $htmlTemplate, $textTemplate = null)
    {
        $template = $htmlTemplate ? array($htmlTemplate, $textTemplate) : array('text' => $textTemplate);
        Mail::send($template, $data, function ($message) use ($data)
        {
            if (is_array($data['to'])) {
                foreach($data['to'] as $key => $value) {
                    $message->to($value)->subject($data['subject']);
                }
            } else {
                $message->to($data['to'])->subject($data['subject']);
            }
        });
    }
}
