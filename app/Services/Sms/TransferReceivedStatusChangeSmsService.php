<?php

/**
 * @package TransferReceivedStatusChangeSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Sms;

use Exception;
use Illuminate\Support\Str;
use App\Services\Sms\TechVillageSms;

class TransferReceivedStatusChangeSmsService extends TechVillageSms
{
    /**
     * The array of status and message whether sms sent or not.
     *
     * @var array
     */
    protected $smsResponse = [];

    public function __construct()
    {
        parent::__construct();
        $this->smsResponse = [
            'status'  => true,
            'message' => __('We have sent you the transfer status. Please check your sms.')
        ];
    }
    /**
     * Send forgot password code to transfer sms
     * @param object $transfer
     * @return array $response
     */
    public function send($transfer, $optional = [])
    {
        $alias = Str::slug('Transfer & Received Status Change');

        try {
            $response = $this->getSmsTemplate($alias);

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{uuid}" => $transfer->uuid,
                "{sender_id/receiver_id}" => getColumnValue($optional['user']),
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['action'],
                "{from/to}" => $optional['fromTo'],
                "{status}" => $transfer->status,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            
            sendSMS($optional['user']->formattedPhone, $message);
        } catch (Exception $e) {
            $this->smsResponse = ['status' => false, 'message' => $e->getMessage()];
        }

        return $this->smsResponse;
    }
}