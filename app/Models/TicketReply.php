<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $table = 'ticket_replies';

    protected $fillable = [
        'admin_id',
        'user_id',
        'ticket_id',
        'message',
        'user_type'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'ticket_reply_id');
    }

    /**
     * create a New Ticket
     *
     * @param  array $data
     * @return object
     */
    public static function replyTicket(array $data): object
    {
        return self::create([
            'admin_id' => $data['admin_id'] ?? auth('admin')->id(),
            'user_id' => $data['user_id'] ?? auth()->id(),
            'ticket_id' => $data['ticket_id'],
            'user_type' => $data['user_type'] ?? 'admin',
            'message' => $data['message']
        ]);
    }
}
