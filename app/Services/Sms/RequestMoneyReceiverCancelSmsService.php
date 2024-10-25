<?php

/**
 * @package RequestMoneyReceiverCancelSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Sms;

use Exception;


class RequestMoneyReceiverCancelSmsService extends TechVillageSms
{
    /**
     * The array of status and message whether sms sent or not.
     *
     * @var array
     */
    protected $response = [];

    public function __construct()
    {
        parent::__construct();
        $this->response = [
            'status'  => true,
            'message' => __("Request Cancelled by the request creator. A sms is sent to the request receiver.")
        ];
    }
    
    /**
     * Send sms to request receiver 
     * @param object $requestPayment
     * @return array $response
     */
    public function send($requestPayment)
    {
        try {
            $sms = $this->getTemplate(33);
            if (!$sms['status']) {
                return $sms;
            }
            $phoneNumber   = !empty($requestPayment->receiver_id) ? optional($requestPayment->receiver)->phone_number : $requestPayment->phone;
            $receiverName  = !empty($requestPayment->receiver_id) ? optional($requestPayment->receiver)->full_name : $requestPayment->phone;
            $data = [
                '{user}'         => $receiverName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{cancelled_by}' => $requestPayment->user->full_name,
                '{soft_name}'    => settings('name'),
            ];
            $message = str_replace(array_keys($data), $data, $sms['template']->body);
            sendSMS($phoneNumber, $message);
        } catch (Exception $e) {
            $this->response['message'] = $e->getMessage();
        }
        return $this->response;
    }

}
