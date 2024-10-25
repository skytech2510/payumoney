<?php

namespace App\Services\Mail\Ticket;

use Exception;
use App\Services\Mail\TechVillageMail;


class NewTicketMailService extends TechVillageMail
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
            'message' => __('We have sent you a new ticket message. Please check your email.')
        ];
    }
    /**
     * Send New ticket message to user email
     * @param object $ticket
     * @param array $optional
     * @return array $response
     */
    public function send($ticket, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('new-ticket');
            
            if (!$response['status']) {
                return $response;
            }

            $data = [
                '{admin/assignee/user}' => getColumnValue($optional['to']),
                '{ticket_code}' => $ticket->code,
                '{assigned/created}' => $optional['type'],
                '{to/for}' => $optional['toFor'],
                '{created_at}' => dateFormat(now()),
                '{assignee}' => $optional['assignee'],
                '{user}' => $optional['user'],
                '{subject}' => $ticket->subject,
                '{message}' => $ticket->message,
                '{status}' => optional($ticket->ticket_status)->name,
                '{priority}' => $ticket->priority,
                '{soft_name}' => settings('name')
            ];

            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $subject = str_replace('{assigned/created}', $optional['type'], $response['template']->subject);

            $this->email->sendEmail($optional['to']->email, $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}
