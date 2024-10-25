<?php

/**
 * @package DepositViaAdminMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail\Withdrawal;

use Exception;
use App\Services\Mail\TechVillageMail;

class NotifyAdminOnWithdrawMailService extends TechVillageMail
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
            'message' => __('We have sent you the deposit details. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to deposit email
     * @param object $deposit
     * @return array $response
     */
    public function send($withdraw, $optional = [])
    {
        $recipient = getRecipientFromNotificationSetting($optional);

        try {
            $response = $this->getEmailTemplate('notify-admin-on-withdrawal');

            if (!$response['status']) {
                return $response;
            }
        
            $data = [
                "{admin}" => $recipient['name'] ?? $recipient['email'],
                "{user}" => getColumnValue($withdraw->user),
                "{created_at}" => dateFormat($withdraw->created_at, $withdraw->user_id),
                "{payment_method}" => $withdraw->payment_method?->name,
                "{uuid}" => $withdraw->uuid,
                "{code}" => $withdraw->currency?->code,
                "{amount}" => moneyFormat(optional($withdraw->currency)->symbol, formatNumber($withdraw->amount)),
                "{fee}" => moneyFormat(optional($withdraw->currency)->symbol, formatNumber($withdraw->charge_fixed + $withdraw->charge_percentage)),
                "{soft_name}" => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($recipient['email'], $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}