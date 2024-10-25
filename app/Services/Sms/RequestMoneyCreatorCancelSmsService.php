<?php

/**
 * @package RequestMoneyCreatorCancelSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Sms;

use Exception;


class RequestMoneyCreatorCancelSmsService extends TechVillageSms
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
            'message' => __("Request Cancelled by the request receiver. A sms is sent to the request creator.")
        ];
    }
    
    /**
     * Send sms to request creator 
     *
     * @param object $requestPayment
     * @return array
     */
    public function send($requestPayment)
    {
        try {
            $sms = $this->getTemplate(35);
            if (!$sms['status']) {
                return $sms;
            }
            $phoneNumber  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->formattedPhone : $requestPayment->phone;
            $creatorName  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->full_name : $requestPayment->phone;
            $data = [
                '{creator}'      => $creatorName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{cancelled_by}' => $requestPayment->receiver->full_name,
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
