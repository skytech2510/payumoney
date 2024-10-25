<?php

/**
 * @package WithdrawalStatusChangeMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class WithdrawalStatusChangeMailService extends TechVillageMail
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
            'message' => __('We have sent you the withdrawal status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to withdrawal email
     * @param object $withdrawal
     * @return array $response
     */
    public function send($withdrawal, $optional = [])
    {
        $alias = Str::slug('Withdrawal Status Change');
        try {
            $response = $this->getEmailTemplate($alias);

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
            $subject = str_replace("{uuid}", $withdrawal->uuid, $response['template']->subject);

            $this->email->sendEmail($withdrawal?->user?->email, $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}