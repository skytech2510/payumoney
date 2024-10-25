<?php

/**
 * @package RequestMoneyAcceptorMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Mail;

use Exception;


class RequestMoneyAcceptorMailService extends TechVillageMail
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
            'message' => __("Request Cancelled by the request receiver. An email is sent to the request creator.")
        ];
    }

    /**
     * Send email to request creator
     *
     * @param object $requestPayment
     * @return array
     */
    public function send($requestPayment)
    {
        try {
            $response = $this->getEmailTemplate(4);
            if (!$response['status']) {
                return $response;
            }
            if (is_null($requestPayment->receiver_id)) {
               return $response;
            }
            $acceptorEmail = optional($requestPayment->receiver)->email;
            $acceptorName =  optional($requestPayment->receiver)->full_name;
            $creatorName  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->full_name : $requestPayment->email;
            $data = [
                '{creator}'      => $creatorName,
                '{acceptor}'     => $acceptorName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{note}'         => $requestPayment->note,
                '{created_at}'   => dateFormat(now()),
                '{soft_name}'    => settings('name'),
            ];
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($acceptorEmail, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }

}
