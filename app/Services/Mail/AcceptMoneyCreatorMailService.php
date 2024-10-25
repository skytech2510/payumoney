<?php

/**
 * @package AcceptMoneyCreatorMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Mail;

use Exception;
use Illuminate\Support\Str;


class AcceptMoneyCreatorMailService extends TechVillageMail
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
        $alias = Str::slug('Request Payment Acceptance');
        try {
            $response = $this->getEmailTemplate($alias);
            if (!$response['status']) {
                return $response;
            }

            $creatorEmail = !empty($requestPayment->user_id) ? optional($requestPayment->user)->email : $requestPayment->email;
            $creatorName  = !empty($requestPayment->user_id) ? optional($requestPayment->user)->full_name : $requestPayment->email;
            $acceptorName  = !empty($requestPayment->receiver_id ) ? optional($requestPayment->receiver)->full_name : optional($requestPayment->receiver)->email;
            $data = [
                '{creator}'      => $creatorName,
                '{acceptor}'     => $acceptorName,
                '{uuid}'         => $requestPayment->uuid,
                '{amount}'       => moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->amount)),
                '{accept_amount}'=> moneyFormat(optional($requestPayment->currency)->symbol, formatNumber($requestPayment->accept_amount)),
                '{currency}'     => optional($requestPayment->currency)->code,
                '{created_at}'   => $requestPayment->created_at,
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
