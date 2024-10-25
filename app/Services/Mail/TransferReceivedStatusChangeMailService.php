<?php

/**
 * @package TransferReceivedStatusChangeMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 01-06-2023
 */

namespace App\Services\Mail;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class TransferReceivedStatusChangeMailService extends TechVillageMail
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
            'message' => __('We have sent you the transfer status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to transfer email
     * @param object $transfer
     * @return array $response
     */
    public function send($transfer, $optional = [])
    {
        $alias = Str::slug('Transfer & Received Status Change');
        try {
            $response = $this->getEmailTemplate($alias);

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
            $subject = str_replace("{uuid}", $transfer->uuid, $response['template']->subject);

            $this->email->sendEmail($optional['user']->email, $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}