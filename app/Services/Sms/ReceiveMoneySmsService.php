<?php

/**
 * @package ReceiveMoneySmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Alam <[ashraful.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Sms;

use Exception;

class ReceiveMoneySmsService extends TechVillageSms
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
     * @return array $smsResponse
     */
    public function send($requestMoney, $optional = [])
    {
        try {
            $response = $this->getSmsTemplate('notify-request-receiver');
            
            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{request_receiver}" => (!empty($requestMoney->receiver)) ? getColumnValue($requestMoney->receiver) : __('Sir'),
                "{request_sender}" => getColumnValue($requestMoney->user),
                "{amount}" => moneyFormat(optional($requestMoney->currency)->symbol, formatNumber($requestMoney->amount, $requestMoney->currency_id)),
                "{uuid}" => $requestMoney->uuid,
                "{created_at}" => dateFormat($requestMoney->created_at),
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            sendSMS($requestMoney->phone, $message);
        } catch (Exception $e) {
            $this->smsResponse['message'] = $e->getMessage();
        }
        return $this->smsResponse;
    }

}
