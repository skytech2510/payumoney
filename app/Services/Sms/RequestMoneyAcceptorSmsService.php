<?php

/**
 * @package RequestMoneyAcceptorSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Alam <[ashraful.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Sms;

use Exception;


class RequestMoneyAcceptorSmsService extends TechVillageSms
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
            'message' => __("Request Accepted by the request receiver. A sms is sent to the request creator.")
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
            $sms = $this->getTemplate(4);
            if ($sms['status']) {
                return $sms;
            }
            if (is_null($requestPayment->receiver_id)) {
                return $sms;
            }
            $phoneNumber  = optional($requestPayment->receiver)->formattedPhone;
            $creatorName  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->full_name : $requestPayment->phone;
            $data = [
                '{creator}'      => $creatorName,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{acceptor}'     => optional($requestPayment->receiver)->full_name,
            ];
            $message = str_replace(array_keys($data), $data, $sms['template']->body);
            sendSMS($phoneNumber, $message);
        } catch (Exception $e) {
            $this->response['message'] = $e->getMessage();
        }
        return $this->response;
    }

}
