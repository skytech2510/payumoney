<?php

namespace App\Services\Mail\RequestMoney;

use Exception;
use App\Services\Mail\TechVillageMail;

class NotifyAdminOnRequestMoneyMailService extends TechVillageMail
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
            'message' => __('We have sent request money notification. Please check your email.')
        ];
    }
    /**
     * Send request money notification to admin email
     * @param object $requestMoney
     * @return array $optional
     * @return array $response
     */
    public function send($requestMoney, $optional = [])
    {
        $recipient = getRecipientFromNotificationSetting($optional);

        try {
            $response = $this->getEmailTemplate('notify-admin-on-money-received');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{admin}" => $recipient['name'] ?? $recipient['email'],
                "{request_sender}" => getColumnValue($requestMoney->user),
                "{request_receiver}" => is_null($requestMoney->receiver_id) ? $requestMoney->email : getColumnValue($requestMoney->receiver),
                "{uuid}" => $requestMoney->uuid,
                "{code}" => $requestMoney->currency?->code,
                "{request_amount}" => moneyFormat(optional($requestMoney->currency)->symbol, formatNumber($requestMoney->amount, $requestMoney->currency_id)),
                "{given_amount}" => moneyFormat(optional($requestMoney->currency)->symbol, formatNumber($requestMoney->accept_amount, $requestMoney->currency_id)),
                "{fee}" => moneyFormat(optional($requestMoney->currency)->symbol, formatNumber($requestMoney->fee, $requestMoney->currency_id)), 
                "{created_at}" => dateFormat($requestMoney->created_at),
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