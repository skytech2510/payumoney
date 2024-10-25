<?php

/**
 * @package DisputeReplyToDefendantMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 28-05-2023
 */

namespace App\Services\Mail\Dispute;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class DisputeReplyMailService extends TechVillageMail
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
    public function send($discussion, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('dispute-reply');

            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{admin/merchant/user}" => getColumnValue($optional['recipient']),
                "{user}" => $optional['replier'],
                "{created_at}" => dateFormat($discussion->created_at),
                "{transaction_id}" => $discussion?->dispute?->transaction?->uuid,
                "{dispute_id}" => $discussion?->dispute?->code,
                "{claimant}" => getColumnValue($discussion?->dispute?->claimant),
                "{defendant}" => getColumnValue($discussion?->dispute?->defendant),
                "{subject}" => $discussion?->dispute?->title,
                "{message}" => $discussion->message,
                "{status}" => $discussion?->dispute?->status,
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