<?php

/**
 * @package TwoFactorVerificationMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 28-05-2023
 */

namespace App\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;

class TwoFactorVerificationMailService extends TechVillageMail
{
    /**
     * The array of status and message whether email sent or not.
     *
     * @var array
     */
    protected $mailResponse = [];

    public function __construct()
    {
        parent::__construct();
        $this->mailResponse = [
            'status'  => true,
            'message' => __('We have sent you the two-factor authentication code. Please check your email for details.')
        ];
    }
    /**
     * sent two-factor authentication code to user
     * @param object $user
     * @param array $optional
     * @return array $response
     */
    public function send($user, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('two-fa-authentication');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user}" => getColumnValue($user),
                "{code}" => $optional['optCode'],
                "{soft_name}" => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);

            $this->email->sendEmail($user->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}