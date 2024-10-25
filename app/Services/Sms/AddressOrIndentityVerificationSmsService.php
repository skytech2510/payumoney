<?php

/**
 * @package AddressOrIndentityVerificationSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 25-05-2023
 */

namespace App\Services\Sms;

use Exception;

class AddressOrIndentityVerificationSmsService extends TechVillageSms
{
    /**
     * The array of status and message whether sms sent or not.
     *
     * @var array
     */
    protected $smsResponse = [];

    public function __construct()
    {
        parent::__construct();
        $this->smsResponse = [
            'status'  => true,
            'message' => __("we send address or identity verification sms to your phone.")
        ];
    }

    /**
     * Send address or identity verification sms
     *
     * @param object $requestPayment
     * @param array $optional
     * @return array $smsResponse
     */
    public function send($user, $optional = [])
    {
        try {
            $response = $this->getSmsTemplate('address-or-identity-verification');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{Identity/Address}" => $optional['type'],
                "{user}" => getColumnValue($user),
                "{approved/pending/rejected}" => ucfirst($optional['status']),
                "{soft_name}" => settings('name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            sendSMS($user->formattedPhone, $message);

        } catch (Exception $e) {
            $this->smsResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->smsResponse;
    }

}
