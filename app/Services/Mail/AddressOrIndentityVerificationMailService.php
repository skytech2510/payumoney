<?php

/**
 * @package AddressOrIndentityVerificationMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 28-05-2023
 */

namespace App\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;

class AddressOrIndentityVerificationMailService extends TechVillageMail
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
            'message' => __('Your address/identity verification has been updated. Please check your email.')
        ];
    }
    /**
     * Address or Identity Verification status to user email
     * @param object $user
     * @param array $optional
     * @return array $response
     */
    public function send($user, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('address-or-identity-verification');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{user}" => getColumnValue($user),
                "{Identity/Address}" => $optional['type'],
                "{approved/pending/rejected}" => ucfirst($optional['status']),
                "{soft_name}" => settings('name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $subject = str_replace("{Identity/Address}", $optional['type'], $response['template']->subject);

            $this->email->sendEmail($user->email, $subject, $message);

        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}