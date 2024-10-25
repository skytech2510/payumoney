<?php

/**
 * @package RequestPaymentStatusChangeSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 30-05-2023
 */

namespace App\Services\Sms;

use Exception;
use Illuminate\Support\Str;
use App\Services\Sms\TechVillageSms;

class RequestPaymentStatusChangeSmsService extends TechVillageSms
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
            'message' => __('We have sent you the request money status. Please check your sms.')
        ];
    }
    /**
     * Send forgot password code to request money sms
     * @param object $requestMoney
     * @return array $response
     */
    public function send($requestMoney, $optional = [])
    {
        $alias = Str::slug('Request Payment Status Change');
        try {
            $response = $this->getSmsTemplate($alias);

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{uuid}" => $requestMoney->uuid,
                "{user_id/receiver_id}" => getColumnValue($optional['user']),
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['status'],
                "{from/to}" => $optional['fromTo'],
                "{status}" => $requestMoney->status,
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