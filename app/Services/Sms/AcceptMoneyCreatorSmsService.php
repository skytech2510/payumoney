<?php

/**
 * @package AcceptMoneyCreatorSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Alam <[ashraful.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Sms;

use Exception;


class AcceptMoneyCreatorSmsService extends TechVillageSms
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
            'message' => __("Request Accepted by the request receiver. A sms is sent to the request creator.")
        ];
    }

    /**
     * Send sms to request creator
     *
     * @param object $requestPayment
     * @param array $optional
     * @return array response
     */
    public function send($requestPayment, $optional = [])
    {
        try {
            $response = $this->getSmsTemplate('notify-request-sender-on-money-received');
            
            if (!$response['status']) {
                return $response;
            }

            $phoneNumber  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->formattedPhone : $requestPayment->phone;
            $creatorName  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->full_name : $requestPayment->phone;
            $data = [
                '{request_sender}'   => $creatorName,
                '{uuid}'             => $requestPayment->uuid,
                '{amount}'           => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount, $requestPayment->currency_id)),
                '{request_receiver}' => optional($requestPayment->receiver)->full_name,
                '{soft_name}'        => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            sendSMS($phoneNumber, $message);
        } catch (Exception $e) {
            $this->smsResponse['message'] = $e->getMessage();
        }

        return $this->smsResponse;
    }

}
