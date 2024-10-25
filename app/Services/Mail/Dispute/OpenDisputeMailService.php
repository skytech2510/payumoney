<?php

namespace App\Services\Mail\Dispute;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class OpenDisputeMailService extends TechVillageMail
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
            'message' => __('We have sent you the dispute reply. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to dispute email
     * @param object $discussion
     * @return array $response
     */
    public function send($dispute, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('open-dispute');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{admin/merchant}" => getColumnValue($optional['recipient']),
                "{created_at}" => dateFormat($dispute->created_at),
                "{claimant}" => getColumnValue($dispute->claimant),
                "{defendant}" => getColumnValue($dispute->defendant),
                "{dispute_id}" => $dispute->code,
                "{uuid}" => $dispute?->transaction?->uuid,
                "{subject}" => $dispute->title,
                "{description}" => $dispute->description,
                "{status}" => $dispute->status,
                "{soft_name}" => settings('name'),
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $this->email->sendEmail($optional['recipient']->email, $response['template']->subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}