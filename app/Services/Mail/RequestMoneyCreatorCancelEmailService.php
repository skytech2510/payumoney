<?php

/**
 * @package RequestMoneyCreatorCancelEmailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Mail;

use Exception;


class RequestMoneyCreatorCancelEmailService extends TechVillageMail
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
            $response = $this->getEmailTemplate(34);
            if (!$response['status']) {
                return $response;
            }
            $creatorEmail = !empty($requestPayment->user_id) ? optional($requestPayment->user)->email : $requestPayment->email;
            $creatorName  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->full_name : $requestPayment->email;
            $data = [
                '{creator}'      => $creatorName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{cancelled_by}' => $requestPayment->receiver->full_name,
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
