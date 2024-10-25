<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'admin_id',
        'user_id',
        'ticket_id',
        'ticket_reply_id',
        'filename',
        'originalname',
        'type',
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
        return $this->hasOne(Ticket::class, 'file_id');
    }

    public function ticket_reply()
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'file_id');
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class, 'file_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'file_id');
    }

    public function document_verification()
    {
        return $this->hasOne(DocumentVerification::class, 'file_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'file_id');
    }

    // Mobile Money
    public static function add(array $fields)
    {
        return self::create($fields);
    }
    public static function createOrUpdate(array $conditions, array $updates)
    {
        return self::updateOrCreate($conditions, $updates);
    }
}
