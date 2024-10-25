<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Users\EmailController;
use App\DataTables\Admin\TicketsDataTable;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Session, Common;
use App\Models\{TicketReply,
    TicketStatus,
    Ticket,
    Admin,
    File,
    User
};
use App\Services\Mail\Ticket\{NewTicketMailService,
    TicketReplyMailService
};

class TicketController extends Controller
{
    protected $ticket, $email, $helper;
    public function __construct(Ticket $ticket, EmailController $email, Common $helper)
    {
        $this->ticket = $ticket;
        $this->email  = $email;
        $this->helper = $helper;
    }

    public function index(TicketsDataTable $dataTable)
    {
        $data['menu'] = 'ticket';

        $data['ticket_status'] = $this->ticket->select('ticket_status_id')->groupBy('ticket_status_id')->get();

        $data['summary'] = Ticket::rightJoin('ticket_statuses', 'ticket_statuses.id', '=', 'tickets.ticket_status_id')
            ->selectRaw('COUNT(tickets.ticket_status_id) as total_status, ticket_statuses.name, ticket_statuses.id, ticket_statuses.color as color')
            ->groupBy('ticket_statuses.id')
            ->get();

        if (isset(request()->ticket_status_id)) {
            $ticket_status_id = request()->ticket_status_id;
            $data['tickets'] = Ticket::where(['ticket_status_id' => $ticket_status_id])->orderBy('id', 'desc')->get();
        } else {
            $data['tickets'] = Ticket::orderBy('id', 'desc')->get();
        }

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['user']     = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->ticket->getTicketsUserName($data['user']);

        return $dataTable->render('admin.tickets.list', $data);
    }

    public function create()
    {
        $data['menu'] = 'ticket';

        $data['admins'] = Admin::where(['status' => 'Active'])->get();

        $data['ticket_statuses'] = TicketStatus::get();

        $data['users'] = User::where(['status' => 'Active'])->get();

        return view('admin.tickets.add', $data);
    }

    public function ticketUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->ticket->getTicketUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'subject' => 'required',
            'message' => 'required',
        ]);

        $ticket = Ticket::createNewTicket($request->all());

        $mailResponse = [];

        if (isset($ticket->admin_id)) {
            $data = [
                'assignee' => getColumnValue($ticket->admin),
                'type' => 'assigned',
                'toFor' => 'to',
                'user' => getColumnValue($ticket->user),
                'to' => $ticket->admin,
            ];

            $mailResponse = (new NewTicketMailService)->send($ticket, $data);
        }

        if (isset($ticket->user_id)) {
            $data = [
                'assignee' => getColumnValue($ticket->admin),
                'type' => 'created',
                'toFor' => 'for',
                'user' => getColumnValue($ticket->user),
                'to' => $ticket->user,
            ];
            $mailResponse = (new NewTicketMailService)->send($ticket, $data);
        }
        
        (!$mailResponse['status']) ? $this->helper->one_time_message('error', $mailResponse['message']) : $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('ticket')]));
        return redirect()->intended(config('adminPrefix').'/tickets/list');
    }

    public function reply($id)
    {
        $data['menu'] = 'ticket';

        $data['ticket'] = Ticket::find($id);
        $data['ticket_status'] = TicketStatus::get();
        $data['ticket_replies'] = TicketReply::where(['ticket_id' => $id])->orderBy('id', 'desc')->get();

        return view('admin.tickets.reply', $data);
    }

    public function change_ticket_status(Request $request)
    {
        if ($request->status_id && $request->ticket_id) {
            $update = Ticket::where(['id' => $request->ticket_id])->update(['ticket_status_id' => $request->status_id]);

            if ($update) {
                $status = TicketStatus::select('name')->where(['id' => $request->status_id])->first();
                $data['message'] = $status->name;
                $data['status']  = '1';
            } else {
                $data['status'] = '0';
            }
            return $data;
        }
    }

    public function adminTicketReply(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'file'    => ['nullable', new CheckValidFile(getFileExtensions(1))],
        ]);

        if (!empty($request->status_id)) {
            Ticket::where(['id' => $request->ticket_id])
            ->update([
                'ticket_status_id' => $request->status_id,
                'last_reply'       => date('Y-m-d H:i:s'),
            ]);
        }

        // Store in Ticket Replies Table
        $ticket_reply = TicketReply::replyTicket($request->all());

        $path = '';

        // Store in Files Table
        if ($request->hasFile('file')) {
            $fileName     = $request->file('file');
            $originalName = $fileName->getClientOriginalName();
            $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
            $file_extn    = strtolower($fileName->getClientOriginalExtension());

            if (checkFileValidation($file_extn, 1)) {
                $path = 'uploads/ticketFile';

                $uploadPath = public_path($path); //problem
                $fileName->move($uploadPath, $uniqueName);

                $file                  = new File();
                $file->admin_id        = auth('admin')->user()->id;
                $file->user_id         = $request->user_id;
                $file->ticket_id       = $request->ticket_id;
                $file->ticket_reply_id = $ticket_reply->id;
                $file->filename        = $uniqueName;
                $file->originalname    = $originalName;
                $file->type            = $file_extn;
                $file->save();
            } else {
                $this->helper->one_time_message('error', __('The :x format is invalid.', ['x' => __('file')]));
            }
        }

        $data = [
            'user' => $ticket_reply->user
        ];

        if ($request->file('file')) {
            $ticket_reply['path'] = $path ?? null;
            $ticket_reply['filename'] = $file->filename ?? null;
            (new TicketReplyMailService)->send($ticket_reply, $data);
        } else {
            (new TicketReplyMailService)->send($ticket_reply, $data);
        }

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('ticket reply')]));
        return redirect()->back();
    }

    public function replyUpdate(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
        ]);

        if (isset($request->id)) {
            $ticket_reply = TicketReply::find($request->id);
            $ticket_reply->message = $request->message;
            $ticket_reply->save();

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('ticket reply')]));
            return redirect()->back();
        }
    }

    public function replyDelete(Request $request)
    {
        if (isset($request->id) && isset($request->ticket_id)) {
            //If file exists then delete
            $file = File::where(['ticket_reply_id' => $request->id, 'ticket_id' => $request->ticket_id])->first();

            if (!empty($file)) {
                @unlink(public_path() . '/uploads/ticketFile/' . $file->filename);
                File::where(['ticket_reply_id' => $request->id, 'ticket_id' => $request->ticket_id])->delete();
            }

            //Delete Ticket Reply
            $data = TicketReply::where(['id' => $request->id, 'ticket_id' => $request->ticket_id])->first();
            if (!empty($data)) {
                TicketReply::where(['id' => $request->id, 'ticket_id' => $request->ticket_id])->delete();

                $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('ticket reply')]));
                return redirect()->back();
            }
        }
    }

    public function edit($id)
    {
        $data['menu'] = 'ticket';

        $data['ticket'] = Ticket::find($id);
        $data['user'] = User::where(['id' => $data['ticket']->user_id, 'status' => 'Active'])->first();
        $data['admins'] = Admin::where(['status' => 'Active'])->get();
        $data['ticket_statuses'] = TicketStatus::get();

        return view('admin.tickets.edit', $data);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'subject' => 'required',
            'message' => 'required',
        ]);

        $ticket                   = Ticket::find($request->id);
        $ticket->admin_id         = $request->assignee;
        $ticket->user_id          = $request->user_id;
        $ticket->ticket_status_id = $request->status;
        $ticket->subject          = $request->subject;
        $ticket->message          = $request->message;
        $ticket->priority         = $request->priority;
        $ticket->save();
        
        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('ticket')]));
        return redirect()->intended(config('adminPrefix').'/tickets/list');
    }

    public function delete($id)
    {
        $ticket = Ticket::destroy($id);

        if ($ticket) {
            $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('ticket')]));
            return redirect()->intended(config('adminPrefix') . '/tickets/list');
        }
    }

    public function download($file_name) 
    {
        $file_path = public_path('/uploads/ticketFile/'.$file_name);
        return response()->download($file_path);
    }
}
