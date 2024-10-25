<?php

/**
 * @package EmailToSenderMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ahammed Imtiaze <[imtiaze.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail\SendMoney;

use Exception;
use App\Services\Mail\TechVillageMail;

class NotifyAdminOnSendMoneyMailService extends TechVillageMail
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
            'message' => __('We have sent you the ticket status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to user email
     * @param object $user
     * @return array $response
     */
    public function send($transfer, $optional = [])
    {
        $recipient = getRecipientFromNotificationSetting($optional);

        try {
            $response = $this->getEmailTemplate('notify-admin-on-transfer');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{admin}" => $recipient['name'] ?? $recipient['email'],
                "{sender}" => getColumnValue($transfer->sender),
                "{amount}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->amount, $transfer->currency_id)),
                "{uuid}" => $transfer->uuid,
                "{receiver}" => is_null($transfer->receiver_id) ? $transfer->email : getColumnValue($transfer->receiver), 
                "{fee}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->fee, $transfer->currency_id)), 
                "{created_at}" => dateFormat($transfer->created_at),
                "{note}" => $transfer->note,
                "{soft_name}" => settings('name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($recipient['email'], $response['template']->subject, $message);

        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}