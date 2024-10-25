<?php

/**
 * @package DepositViaAdminMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail\Deposit;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class DepositViaAdminMailService extends TechVillageMail
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
            'message' => __('We have sent you the deposit details. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to deposit email
     * @param object $deposit
     * @return array $response
     */
    public function send($deposit, $optional = [])
    {
        $alias = Str::slug('Notify User on Deposit via Admin');
        try {
            $response = $this->getEmailTemplate($alias);

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user}" => getColumnValue($deposit->user),
                "{created_at}" => dateFormat($deposit->created_at, $deposit->user_id),
                "{uuid}" => $deposit->uuid,
                "{code}" => $deposit->currency?->code,
                "{amount}" => moneyFormat(optional($deposit->currency)->symbol, formatNumber($deposit->amount)),
                "{fee}" => moneyFormat(optional($deposit->currency)->symbol, formatNumber($deposit->charge_fixed + $deposit->charge_percentage)),
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($deposit->user->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}