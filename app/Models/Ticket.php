<?php

namespace App\Models;

use App\Models\File;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'admin_id',
        'user_id',
        'ticket_status_id',
        'subject',
        'message',
        'code',
        'priority',
        'last_reply',
        'status',
    ];

    //belongsTo
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket_status()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }
    //

    //hasOne
    public function file()
    {
        return $this->hasOne(File::class, 'ticket_id');
    }

    public function getTicketUsersResponse($search)
    {
        return User::where('first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('last_name', 'LIKE', '%' . $search . '%')
            ->distinct('first_name')
            ->select('first_name', 'last_name', 'id as user_id', 'email')
            ->get();
    }

    public function getTicketsUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'tickets.user_id')
            ->where(['tickets.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    public function getTicketsList($from, $to, $status, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to))
        {
            $date_range = null;
        }
        else if (empty($from))
        {
            $date_range = null;
        }
        else if (empty($to))
        {
            $date_range = null;
        }
        else
        {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all')
        {
            $conditions['tickets.ticket_status_id'] = $status;
        }

        if (!empty($user))
        {
            $conditions['tickets.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $tickets = $this->with([
                'user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'ticket_status' => function ($query)
                {
                    $query->select('id', 'name');
                },
            ])
            ->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('tickets.created_at', '>=', $from)->whereDate('tickets.created_at', '<=', $to);
            })
            ->select('tickets.*');
        }
        else
        {
            $tickets = $this->with([
                'user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'ticket_status' => function ($query)
                {
                    $query->select('id', 'name');
                },
            ])
            ->where($conditions)
            ->select('tickets.*');
        }
        return $tickets;
    }

    public function latestTicket()
    {
        return $this->leftJoin('ticket_statuses', 'ticket_statuses.id', '=', 'tickets.ticket_status_id')
            ->leftJoin('users', 'users.id', '=', 'tickets.user_id')
            ->where(['ticket_statuses.name' => 'Open'])
            ->select('tickets.*', 'users.first_name', 'users.last_name')
            ->orderBy('tickets.id', 'DESC')
            ->take(5)
            ->get();
    }
    
    /**
     * create a New Ticket
     *
     * @param  array $data
     * @return object
     */
    public static function createNewTicket(array $data): object
    {
        return self::create([
            'admin_id' => $data['admin_id'] ?? Admin::first()->id,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'ticket_status_id' => 1,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'code' => 'TCKT-' . strtoupper(Str::random(8)),
            'priority' => $data['priority']
        ]);
    }
}
