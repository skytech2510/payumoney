<?php

/**
 * @package TransactionUpdatedByAdminSmsService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 31-05-2023
 */

namespace App\Services\Sms;

use Exception;
use Illuminate\Support\Str;
use App\Services\Sms\TechVillageSms;

class TransactionUpdatedByAdminSmsService extends TechVillageSms
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
            'message' => __('We have sent you the transaction status. Please check your sms.')
        ];
    }
    /**
     * Send forgot password code to transaction sms
     * @param object $transaction
     * @return array $response
     */
    public function send($transaction, $optional = []) 
    {
        $alias = Str::slug('Transaction updated by Admin');
        
        try {
            $response = $this->getSmsTemplate($alias);

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{transaction_type}" => str_replace('_', ' ', $transaction?->transaction_type?->name),
                "{uuid}" => $transaction->uuid,
                "{status}" => $transaction->status,
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['action'],
                "{from/to}" => $optional['fromTo'],
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