<?php

/**
 * @package EmailToSenderMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Foisal Ahmed <[foisal.techvill@gmail.com]>
 * @created 16-11-2023
 */

namespace App\Services\Mail\MerchantPayment;

use Exception;
use App\Services\Mail\TechVillageMail;

class NotifyAdminOnMerchantCreationMailService extends TechVillageMail
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
            'message' => __('Merchant creation email notification has been sent successfully.')
        ];
    }
    /**
     * Send forgot password code to user email
     * @param object $user
     * @return array $response
     */
    public function send($merchant, $optional = [])
    {
        $recipient = getRecipientFromNotificationSetting($optional);

        try {
            $response = $this->getEmailTemplate('notify-admin-on-merchant-creation');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{user}" => getColumnValue($merchant->user),
                "{admin}" => $recipient['name'] ?? $recipient['email'],
                "{created_at}" => dateFormat($merchant->created_at),
                "{business_name}" => $merchant->business_name,
                "{code}" => getColumnValue($merchant->currency, 'code', ''), 
                "{site_url}" => $merchant->site_url, 
                "{merchant_type}" => ucfirst($merchant->type), 
                "{message}" => $merchant->note, 
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