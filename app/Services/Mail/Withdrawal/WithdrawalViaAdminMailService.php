<?php

/**
 * @package WithdrawalViaAdminMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail\Withdrawal;

use Exception;
use App\Services\Mail\TechVillageMail;

class WithdrawalViaAdminMailService extends TechVillageMail
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
            'message' => __('We have sent you the withdrawal details. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to withdrawal email
     * @param object $withdrawal
     * @return array $response
     */
    public function send($withdrawal, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('notify-user-on-withdrawal-via-admin');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{uuid}" => $withdrawal->uuid,
                "{user}" => getColumnValue($withdrawal->user),
                "{amount}" => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber($withdrawal->amount)),
                "{code}" => $withdrawal->currency->code,
                "{fee}" => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber($withdrawal->charge_fixed + $withdrawal->charge_percentage)),
                "{created_at}" => dateFormat($withdrawal->created_at, $withdrawal->user_id),
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($withdrawal->user->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}