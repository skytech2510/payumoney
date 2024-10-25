<?php

namespace App\Services\Mail\Ticket;

use Exception;
use App\Services\Mail\TechVillageMail;

class TicketReplyMailService extends TechVillageMail
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
            'message' => __('We have sent you a reply message. Please check your email.')
        ];
    }
    /**
     * Send reply message to user or admin email
     * @param object $reply
     * @param array $optional
     * @return array $response
     */
    public function send($reply, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('ticket-reply');
            
            if (!$response['status']) {
                return $response;
            }

            $data = [
                "{assignee/user}" => getColumnValue($optional['user']),
                "{ticket_code}" => $reply->ticket?->code,
                "{assignee}" => getColumnValue($reply->admin),
                "{message}" => $reply->message,
                "{status}" => $reply->ticket?->ticket_status?->name,
                "{priority}" => $reply->ticket?->priority,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);

            if (isset($reply['filename'])) {
                $this->email->sendEmailWithAttachment($optional['user']->email, $response['template']->subject, $message, $reply['path'], $reply['filename']);
            } else {
                $this->email->sendEmail($optional['user']->email, $response['template']->subject, $message);
            }
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}
