<?php

/**
 * @package SendMoneySmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Alam <[ashraful.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Sms;

use Exception;

class SendMoneySmsService extends TechVillageSms
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
            'message' => __("Transfer amount to receiver. A sms is sent to the sender.")
        ];
    }

    /**
     * Send sms to request creator
     *
     * @param object $requestPayment
     * @param array $optional
     * @return array $smsResponse
     */
    public function send($sendMoney, $optional = [])
    {
        try {
            $response = $this->getSmsTemplate('notify-money-receiver');
            
            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{receiver_id}" => is_null($sendMoney->receiver_id) ? $sendMoney->email : getColumnValue($sendMoney->receiver), 
                "{amount}" => moneyFormat(optional($sendMoney->currency)->symbol, formatNumber($sendMoney->amount, $sendMoney->currency_id)),
                "{sender_id}" => getColumnValue($sendMoney->sender),
                "{uuid}" => $sendMoney->uuid,
                "{created_at}" => dateFormat($sendMoney->created_at),
                "{soft_name}" => settings('name')
            ];

            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            sendSMS($sendMoney->phone, $message);
        } catch (Exception $e) {
            $this->smsResponse['message'] = $e->getMessage();
        }
        return $this->smsResponse;
    }

}
