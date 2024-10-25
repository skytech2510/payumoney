<?php

/**
 * @package EmailToReceiverMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Ahammed Imtiaze <[imtiaze.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail\SendMoney;

use Exception;
use App\Services\Mail\TechVillageMail;

class EmailToReceiverMailService extends TechVillageMail
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
            'message' => __('We have sent you the ticket status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to user email
     * @param object $user
     * @return array $response
     */
    public function send($transfer, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('notify-money-receiver');

            if (!$response['status']) {
                return $response;
            }

            if (is_null($transfer->receiver_id)) {
                $subject = __('Money Received Notification');
                $message = __('Hi <b>:x</b>,', ['x' => explode("@", trim($transfer->email))[0]]) . '<br><br>';
                $message .= __('You have got <b>:x</b> money transfer from <b>:y</b>.', ['x' => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->amount)), 'y' => optional($transfer->sender)->email]) . '<br>';
                $message .= __('To receive the money, please register at <b>:x</b> with this email <b>:y</b>.', ['x' => '<a href="'.url('/register').'">'.url('/register').'</a>', 'y' => '<strong>'.$transfer->email.'</strong>']) . '<br><br>';
                $message .= __('<b><u>Note:</u></b> :x.<br><br>', ['x' => $transfer->note]);
                $message .= __('Regards') . ',<br>';
                $message .= '<b>'.settings('name').'</b>';
            } else {
                $data = [
                    "{receiver_id}" => is_null($transfer->receiver_id) ? $transfer->email : getColumnValue($transfer->receiver), 
                    "{amount}" => moneyFormat(optional($transfer->currency)->symbol, formatNumber($transfer->amount, $transfer->currency_id)),
                    "{sender_id}" => getColumnValue($transfer->sender),
                    "{uuid}" => $transfer->uuid,
                    "{created_at}" => dateFormat($transfer->created_at),
                    "{note}" => $transfer->note,
                    "{soft_name}" => settings('name')
                ];
                $message = str_replace(array_keys($data), $data, $response['template']->body);
                $subject = $response['template']->subject;
            }
           
            $this->email->sendEmail($transfer->email, $subject, $message);

        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}