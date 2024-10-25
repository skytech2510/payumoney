<?php

/**
 * @package SendMoneyMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 20-12-2022
 */

namespace App\Services\Mail;

use Exception;


class SendMoneyMailService extends TechVillageMail
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
            'message' => __("Transfer amount. An email is sent to the sender.")
        ];
    }

    /**
     * Send email to request creator
     *
     * @param object $transfer
     * @return array
     */
    public function send($transfer)
    {
        try {
            $response = $this->getEmailTemplate(1);
            if (!$response['status']) {
                return $response;
            }
            if (is_null($transfer->sender_id)) {
               return $response;
            }
            $senderEmail = optional($transfer->sender)->email;
            $senderName  = optional($transfer->sender)->full_name;
            $receiverName = (!empty($transfer->receiver_id)) ? optional($transfer->receiver)->full_name : $transfer->email ;
            $data = [
                '{sender_id}'      => $senderName,
                '{receiver_id}'     => $receiverName,
                '{uuid}'         => $transfer->uuid,
                '{amount}'       => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->amount)),
                '{fee}'         => $transfer->fee,
                '{created_at}'   => dateFormat(now()),
                '{soft_name}'    => settings('name'),
            ];
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($senderEmail, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }

}
