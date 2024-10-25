<?php

namespace App\Models;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

//pm - 1.7
class DocumentVerification extends Model
{
    protected $table = 'document_verifications';

    protected $fillable = [
        'user_id',
        'file_id',
        'verification_type',
        'identity_type',
        'identity_number',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function getDocumentVerificationsList($from, $to, $status)
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
            $conditions['status'] = $status;
        }

        if (!empty($date_range))
        {
            $document_verifications = $this->with([
            'user' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name');
            }])
            ->where(['verification_type' => 'identity'])->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            })->select('document_verifications.*');
        }
        else
        {
            $document_verifications = $this->with([
            'user' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name');
            }])->where(['verification_type' => 'identity'])->where($conditions)->select('document_verifications.*');
        }
        return $document_verifications;
    }


    public function getAddressVerificationsList($from, $to, $status)
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
            $conditions['status'] = $status;
        }

        if (!empty($date_range))
        {
            $document_verifications = $this->with([
            'user' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name');
            },
            'file' => function ($query)
            {
                $query->select('id', 'filename');
            }])
            ->where(['verification_type' => 'address'])->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            })->select('document_verifications.*');
        }
        else
        {
            $document_verifications = $this->with([
            'user' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name');
            },
            'file' => function ($query)
            {
                $query->select('id', 'filename');
            }])
            ->where(['verification_type' => 'address'])->where($conditions)->select('document_verifications.*');
        }
        return $document_verifications;
    }
}
