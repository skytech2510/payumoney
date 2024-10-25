<?php

/**
 * @package TransactionUpdatedByAdminMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 30-05-2023
 */

namespace App\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;

class TransactionUpdatedByAdminMailService extends TechVillageMail
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
            'message' => __('We have sent you the transaction status update. Please check your email.')
        ];
    }
    /**
     * Send transaction status update to user email
     * @param object $transaction
     * @return array $response
     */
    public function send($transaction, $optional = []) 
    {        
        try {
            $response = $this->getEmailTemplate('transaction-status-update');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user}" => getColumnValue($optional['user']),
                "{transaction_type}" => $optional['type'],
                "{uuid}" => $transaction->uuid,
                "{status}" => $transaction->status,
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['action'],
                "{from/to}" => $optional['fromTo'],
                "{soft_name}" => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);

            $this->email->sendEmail($optional['user']->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}