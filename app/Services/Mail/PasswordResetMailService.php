<?php

namespace App\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;

class PasswordResetMailService extends TechVillageMail
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
            'message' => __('We have sent you the Password reset code. Please check your email.')
        ];
    }
    /**
     * Send Password reset code to admin/user email
     * @param object $userOrAdmin
     * @param array $optional
     * @return array $response
     */
    public function send($userOrAdmin, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('password-reset');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user}" => getColumnValue($userOrAdmin),
                "{email}" => $userOrAdmin->email,
                "{password_reset_url}" => $optional['resetUrl'],
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);

            $this->email->sendEmail($userOrAdmin->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}