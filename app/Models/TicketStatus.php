<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    protected $table    = 'ticket_statuses';

    protected $fillable = [
        'name',
        'color',
        'is_default',
    ];

    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'ticket_status_id');
    }
}
