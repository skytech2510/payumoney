<?php

namespace App\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use App\Http\Helpers\Common;
use App\Models\Ticket;
use Config, Auth;

use Illuminate\Http\JsonResponse;
class TicketsDataTable extends DataTable
{

    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($ticket) {
                return dateFormat($ticket->created_at);
            })->editColumn('user_id', function ($ticket) {
                $user = getColumnValue($ticket->user);
                if ($user <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . $ticket->user->id) . '">' . $user . '</a>';
                }
                return $user;
            })->addColumn('subject', function ($ticket) {
                $subject = $ticket->subject;
                return (Common::has_permission(auth('admin')->user()->id, 'edit_ticket')) ?
                    '<a href="' . url(config('adminPrefix') . '/tickets/reply/' . $ticket->id) . '">' . $subject . '</a>' : $subject;
            })->editColumn('ticket_status_id', function ($ticket) {
                return isset($ticket->ticket_status->name) ? getStatusLabel($ticket->ticket_status->name) : '';
            })->editColumn('last_reply', function ($ticket) {
                return $ticket->last_reply ?  dateFormat($ticket->last_reply)  : __('No Reply Yet');
            })->addColumn('action', function ($ticket) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_ticket')) ? '<a href="' . url(config('adminPrefix') . '/tickets/edit/' . $ticket->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_ticket')) ? '<a href="' . url(config('adminPrefix') . '/tickets/delete/' . $ticket->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>' : '';
                return $edit . $delete;
            })
            ->rawColumns(['user_id', 'subject', 'ticket_status_id', 'action'])
            ->make(true);
    }

    public function query()
    {
        $status = isset(request()->status) ? request()->status : 'all';
        $user   = isset(request()->user_id) ? request()->user_id : null;
        $from   = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to     = isset(request()->to) ? setDateForDb(request()->to) : null;
        $query  = (new Ticket())->getTicketsList($from, $to, $status, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'tickets.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'tickets.created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'subject', 'name' => 'tickets.subject', 'title' => __('Subject')])
            ->addColumn(['data' => 'ticket_status_id', 'name' => 'ticket_status.name', 'title' => __('Status')])
            ->addColumn(['data' => 'priority', 'name' => 'tickets.priority', 'title' => __('Priority')])
            ->addColumn(['data' => 'last_reply', 'name' => 'tickets.last_reply', 'title' => __('Last Reply')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
