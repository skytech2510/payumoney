<?php

namespace App\Services\Mail\RequestMoney;

use Exception;
use App\Services\Mail\TechVillageMail;

class RequestReceiverMailService extends TechVillageMail
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
            'message' => __('We have sent you a request for money. Please check your email.')
        ];
    }
    /**
     * Send request for money to user email
     * @param object $requestMoney
     * @param array $optional
     * @return array $response
     */
    public function send($requestMoney, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('notify-request-receiver');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{request_receiver}" => (!empty($requestMoney->receiver)) ? getColumnValue($requestMoney->receiver) : explode("@", trim($requestMoney->email))[0],
                "{request_sender}" => getColumnValue($requestMoney->user),
                "{amount}" => moneyFormat(optional($requestMoney->currency)->symbol, formatNumber($requestMoney->amount, $requestMoney->currency_id)),
                "{uuid}" => $requestMoney->uuid,
                "{created_at}" => dateFormat($requestMoney->created_at),
                "{note}" => $requestMoney->note,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);

            $this->email->sendEmail($requestMoney->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}