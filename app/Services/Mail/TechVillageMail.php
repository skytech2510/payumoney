<?php

namespace App\Services\Mail;

use App\Http\Controllers\Users\EmailController;
use App\Models\{
    EmailTemplate
};

abstract class TechVillageMail
{
    protected $email;
    /**
     * The array of status and message whether email template found or not
     *
     * @var array
     */
    protected $response = [];

    abstract protected function send($request);

    /**
     * Constructor
     *
     * return void
     */
    public function __construct()
    {
        $this->email = new EmailController();
        $this->response = [
            'status'  => false, 
            'message' => __('Email can not be sent, please contact the website administrator.')
        ];
    }

    /**
     * Get Email Template based on tempId
     *
     * @param int tempId
     * @return array
     */
    protected function getEmailTemplate($alias)
    {
        if (!isMailEnabled() || empty($alias)) {
            return $this->response;
        }

        $template = EmailTemplate::alias($alias)->defaultLanguage()->type('email')->first(['subject', 'body']);
        if (empty($template->subject) || empty($template->body)) {
            $template = EmailTemplate::alias($alias)->englishLanguage()->type('email')->first(['subject', 'body']);
        }
        
        if (!$template) {
            return $this->response;
        }

        return $this->response = [
            'status' => true, 
            'template' => $template
        ];
    }
}
