<?php

/**
 * @package RequestPaymentStatusChangeMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 30-05-2023
 */

namespace App\Services\Mail;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class RequestPaymentStatusChangeMailService extends TechVillageMail
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
            'message' => __('We have sent you the request money status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to request money email
     * @param object $requestMoney
     * @return array $response
     */
    public function send($requestMoney, $optional = [])
    {
        $alias = Str::slug('Request Payment Status Change');
        try {
            $response = $this->getEmailTemplate($alias);

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
            $subject = str_replace("{uuid}", $requestMoney->uuid, $response['template']->subject);

            $this->email->sendEmail($optional['user']->email, $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}