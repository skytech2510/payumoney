<?php

/**
 * @package EmailToSenderMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ahammed Imtiaze <[imtiaze.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail\MerchantPayment;

use Exception;
use App\Services\Mail\TechVillageMail;

class NotifyMerchantOnPaymentMailService extends TechVillageMail
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
            'message' => __('Merchant payment email notification has been sent successfully.')
        ];
    }
    /**
     * Send forgot password code to user email
     * @param object $user
     * @return array $response
     */
    public function send($payment, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('notify-merchant');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{amount}" => moneyFormat(optional($payment->currency)->symbol, formatNumber($payment->amount, $payment->currency_id)),
                "{merchant}" => getColumnValue($payment->merchant, 'business_name', ''),
                "{user}" => $payment->payment_method_id == Mts ? getColumnValue($payment->user) : __('Unregistered User'),
                "{created_at}" => dateFormat($payment->created_at),
                "{uuid}" => $payment->uuid,
                "{code}" => getColumnValue($payment->currency, 'code', ''), 
                "{fee}" => moneyFormat(optional($payment->currency)->symbol, formatNumber($optional['fee'], $payment->currency_id)), 
                "{fee_bearer}" => $optional['fee_bearer'], 
                "{soft_name}" => settings('name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($payment?->merchant?->user?->email, $response['template']->subject, $message);

        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}