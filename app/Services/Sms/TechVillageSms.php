<?php

namespace App\Services\Sms;

use App\Models\{
    EmailTemplate
};

abstract class TechVillageSms
{
    /**
     * The array of status and message whether sms template found or not
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
        $this->response = [
            'status'  => false, 
            'message' => __('SMS can not be sent, please contact the website administrator.')
        ];
    }

    /**
     * Get Email Template based on alias
     *
     * @param int alias
     * @return array
     */
    protected function getSmsTemplate($alias)
    {
        if (!isSmsEnabled() || empty($alias)) {
            return $this->response;
        }

        $template = EmailTemplate::alias($alias)->defaultLanguage()->type('sms')->first(['subject', 'body']);
        if (empty($template->subject) || empty($template->body)) {
            $template = EmailTemplate::alias($alias)->englishLanguage()->type('sms')->first(['subject', 'body']);
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
