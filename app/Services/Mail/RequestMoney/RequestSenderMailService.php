<?php

namespace App\Services\Mail\RequestMoney;

use Exception;
use App\Services\Mail\TechVillageMail;

class RequestSenderMailService extends TechVillageMail
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
            'message' => __("Request Accepted by the receiver. An email is sent to the request creator.")
        ];
    }

    /**
     * Send email to request creator
     *
     * @param object $requestPayment
     * @return array
     */
    public function send($requestPayment, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('notify-request-sender-on-money-received');
            
            if (!$response['status']) {
                return $response;
            }

            $creatorEmail = !empty($requestPayment->user_id) ? optional($requestPayment->user)->email : $requestPayment->email;
            $creatorName  = !empty($requestPayment->user_id) ? getColumnValue($requestPayment->user) : $requestPayment->email;
            $acceptorName  = !empty($requestPayment->receiver_id ) ? getColumnValue($requestPayment->receiver) : optional($requestPayment->receiver)->email;
            
            $data = [
                '{request_sender}'      => $creatorName,
                '{request_receiver}'    => $acceptorName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount, $requestPayment->currency_id)),
                '{accept_amount}'=> moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->accept_amount, $requestPayment->currency_id)),
                '{currency}'     => optional($requestPayment->currency)->code,
                '{created_at}'   => dateFormat($requestPayment->created_at),
                '{soft_name}'    => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);

            $this->email->sendEmail($creatorEmail, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}