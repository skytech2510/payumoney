<?php

/**
 * @package MerchantPaymentStatusChangeMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 30-05-2023
 */

namespace App\Services\Mail;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class MerchantPaymentStatusChangeMailService extends TechVillageMail
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
            'message' => __('We have sent you the merchant payment status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to merchant payment email
     * @param object $merchantPayment
     * @return array $response
     */
    public function send($merchantPayment, $optional = [])
    {
        $alias = Str::slug('Merchant Payment Status Change');
        try {
            $response = $this->getEmailTemplate($alias);

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{uuid}" => $merchantPayment->uuid,
                "{paidByUser/merchantUser}" => getColumnValue($optional['user']),
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['status'],
                "{from/to}" => $optional['fromTo'],
                "{status}" => $merchantPayment->status,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $subject = str_replace("{uuid}", $merchantPayment->uuid, $response['template']->subject);

            $this->email->sendEmail($optional['user']->email, $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}