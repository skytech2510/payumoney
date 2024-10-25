<?php

/**
 * @package WithdrawalStatusChangeSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Sms;

use Exception;
use Illuminate\Support\Str;
use App\Services\Sms\TechVillageSms;

class WithdrawalStatusChangeSmsService extends TechVillageSms
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
            'message' => __('We have sent you the withdrawal status. Please check your sms.')
        ];
    }
    /**
     * Send forgot password code to withdrawal sms
     * @param object $withdrawal
     * @return array $response
     */
    public function send($withdrawal, $optional = [])
    {
        $alias = Str::slug('Withdrawal Status Change');
        try {
            $response = $this->getSmsTemplate($alias);

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user_id}" => getColumnValue($withdrawal->user),
                "{uuid}" => $withdrawal->uuid,
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['status'],
                "{from/to}" => $optional['fromTo'],
                "{status}" => $withdrawal->status,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            sendSMS($withdrawal?->user?->formattedPhone, $message);
        } catch (Exception $e) {
            $this->smsResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->smsResponse;
    }
}