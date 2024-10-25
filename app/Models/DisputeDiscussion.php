<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeDiscussion extends Model
{
    protected $table   = 'dispute_discussions';
    public $timestamps = true;

    public function dispute()
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

}
