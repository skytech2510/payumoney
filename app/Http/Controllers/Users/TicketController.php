<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Common, Exception, DB;
use App\Models\{Admin,
    TicketStatus,
    TicketReply,
    Ticket,
    File
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

    public function index()
    {
        $status = isset(request()->status) ? request()->status : 'all';

        $data = [
            'menu' => 'ticket',
            'status' => $status,
            'statuses' => TicketStatus::get(['id', 'name']),
            'tickets' => $this->ticket->getTicketsList('' , '' , $status, auth()->id())->latest()
            ->paginate(10),
        ];
            
        return view('user.ticket.index', $data);
    }

    public function create()
    {
        $data['menu'] = 'ticket';
        return view('user.ticket.create', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'subject'     => 'required',
            'message' => 'required',
        ]);

        $admin = Admin::first();
        $request['admin_id'] = $admin->id;
        $ticket = Ticket::createNewTicket($request->all());

        $data = [
            'assignee' => getColumnValue($admin),
            'type' => 'created',
            'toFor' => 'for',
            'user' => getColumnValue(auth()->user()),
            'to' => $admin,
        ];

        (new NewTicketMailService)->send($ticket, $data);

        $this->helper->one_time_message('success', __('Ticket Created Successfully!'));

        return redirect()->route('user.tickets.reply', $ticket->id);
    }

    public function reply(Request $request, $id)
    {
        $data['menu'] = 'ticket';
        $data['ticket'] = Ticket::with(['ticket_status:id,name','user:id,first_name,last_name,picture'])->find($id);
        $data['ticketStatuses'] = TicketStatus::get(['id','name']);
        
        if ($request->ajax()) {
            $data['ticketReplies'] = TicketReply::with(['file:id,ticket_reply_id,filename,originalname','user:id,first_name,last_name,picture','admin:id,first_name,last_name,picture'])
            ->where(['ticket_id' => $id])->orderBy('id', 'desc')->paginate(5);

            return view('user.ticket.reply_loadmore', $data)->render();
        }

        return view('user.ticket.reply', $data);
    }

    public function reply_store(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'file' => ['nullable', new CheckValidFile(getFileExtensions(1), true)],
        ]);

        try {
            DB::beginTransaction();

            $ticket = Ticket::find($request->ticket_id,['id','ticket_status_id','last_reply','admin_id']);
            $ticket->ticket_status_id = $request->status_id;
            $ticket->last_reply = date('Y-m-d H:i:s');
            $ticket->save();

            // Store in Ticket Replies Table
            $request['admin_id'] = $ticket->admin_id;
            $request['user_type'] = 'user';
            $ticket_reply = TicketReply::replyTicket($request->all());

            // Store in Files Table
            if ($request->hasFile('file')) {
                $fileName     = $request->file('file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn    = strtolower($fileName->getClientOriginalExtension());

                $path = 'uploads/ticketFile';
                $uploadPath = public_path($path);

                $fileName->move($uploadPath, $uniqueName);
                
                $file                  = new File();
                $file->admin_id        = $ticket->admin_id;
                $file->user_id         = auth()->id();
                $file->ticket_id       = $request->ticket_id;
                $file->ticket_reply_id = $ticket_reply->id;
                $file->filename        = $uniqueName;
                $file->originalname    = $originalName;
                $file->type            = $file_extn;
                $file->save();
            }
            DB::commit();
    
            $data = [
                'user' => $ticket_reply->admin
            ];

            if ($request->file('file')) {
                $ticket_reply['path'] = $path ?? null;
                $ticket_reply['filename'] = $file->filename ?? null;
                (new TicketReplyMailService)->send($ticket_reply, $data);
            } else {
                (new TicketReplyMailService)->send($ticket_reply, $data);
            }
            
            $this->helper->one_time_message('success', __('Ticket Reply Saved Successfully!'));
            return redirect()->back();

        } catch (Exception $e) {
            DB::rollback();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->back();
        }
    }

    public function changeReplyStatus(Request $request)
    {
        $ticket                   = Ticket::find($request->ticket_id,['id','ticket_status_id']);
        $ticket->ticket_status_id = $request->status_id;
        $ticket->save();

        $status = TicketStatus::find($request->status_id,['id','name']);
        $data['status'] = $status->name;
        return redirect()->route('user.tickets.reply', $ticket->id);
    }

    public function download($file_name) {
        $file_path = public_path('/uploads/ticketFile/'.$file_name);
        return response()->download($file_path);
      }
}
