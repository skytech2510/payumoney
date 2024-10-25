<?php

/**
 * @package UserStatusChangeMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;

class UserStatusChangeMailService extends TechVillageMail
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
            'message' => __('We have sent you a notification regarding the change in your profile status. Please check your email for further details.')
        ];
    }
    /**
     * Send notification regarding the change in your profile status to user email
     * @param object $user
     * @param array $optional
     * @return array $response
     */
    public function send($user, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('profile-status-change');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user}" => getColumnValue($user),
                "{status}" => $user->status,
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