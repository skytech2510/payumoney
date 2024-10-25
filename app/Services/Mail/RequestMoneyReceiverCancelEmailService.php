<?php

/**
 * @package RequestMoneyReceiverCancelEmailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Mail;

use Exception;


class RequestMoneyReceiverCancelEmailService extends TechVillageMail
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
            'message' => __("Request Cancelled by the request creator. An email is sent to the request receiver.")
        ];
    }
    
    /**
     * Send email to request receiver
     *
     * @param object $requestPayment
     * @return array
     */
    public function send($requestPayment)
    {
        try {
            $response = $this->getEmailTemplate(32);
            if (!$response['status']) {
                return $response;
            }
            $receiverEmail = !empty($requestPayment->receiver_id) ? optional($requestPayment->receiver)->email : $requestPayment->email;
            $receiverName  = !empty($requestPayment->receiver_id) ? optional($requestPayment->receiver)->full_name : $requestPayment->email;
            $data = [
                '{user}'         => $receiverName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{cancelled_by}' => $requestPayment->user->full_name,
                '{soft_name}'    => settings('name'),
            ];
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($receiverEmail, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }

}
