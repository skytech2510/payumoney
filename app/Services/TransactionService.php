<?php

/**
 * @package TransactionService
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 08-12-2022
 */

namespace App\Services;

use App\Exceptions\Api\V2\TransactionException;
use App\Http\Resources\V2\{
    TransactionDetailResource,
    TransactionCollection
};
use App\Models\Transaction;

class TransactionService
{

    /**
     * All transaction list
     *
     * @param string $type
     * @param integer $userId
     * @param integer $offset
     * @param integer $limit
     * @param string $order
     * @return array (transactions, totalRecords)
     */
    public function list($type="allTransactions", $userId, $offset = 0, $limit = 10, $order = "desc") : array
    {
        $data       = ['transactions' => [], 'totalRecords' => 0];
        $conditions = ['transactions.user_id' => $userId];
        if ('allTransactions' == $type) {
            $type = (new Transaction)::$transactionTypes;
        }
        $transaction = Transaction::with([
            'currency:id,type,code,symbol',
            'user:id,first_name,last_name,picture',
            'end_user:id,first_name,last_name,picture',
            'payment_method:id,name',
            'transaction_type:id,name',
            'merchant:id,business_name,logo',
            'bank:id,bank_name,file_id',
            'bank.file:id,filename',
        ])
        ->where($conditions)
        ->whereIn('transactions.transaction_type_id', $type)
        ->orderBy('transactions.id', $order)
        ->select([
            'transactions.id as id',
            'transactions.user_id',
            'transactions.end_user_id',
            'transactions.currency_id',
            'transactions.payment_method_id',
            'transactions.merchant_id',
            'transactions.bank_id',
            'transactions.transaction_type_id',
            'transactions.subtotal as subtotal',
            'transactions.charge_percentage as charge_percentage',
            'transactions.charge_fixed as charge_fixed',
            'transactions.total as total',
            'transactions.status as status',
            'transactions.email as email',
            'transactions.phone as phone',
            'transactions.note as note',
            'transactions.uuid as uuid',
            'transactions.transaction_reference_id as transaction_reference_id',
            'transactions.created_at as t_created_at',
        ]);

        $totalRecords         = $transaction->count();
        $transactions         = $transaction->offset($offset)->limit($limit)->get();
        $data['transactions'] = new TransactionCollection($transactions);
        $data['totalRecords'] = $totalRecords;
        return $data;
    }


    /**
     * Get transaction details based on id
     *
     * @param int $trId
     * @param int $userId
     * @return TransactionDetailResource
     * @throws TransactionException
     */
    public function details($trId, $userId) : TransactionDetailResource
    {
        $conditions       = ['transactions.id' => $trId, 'transactions.user_id' => $userId];
        $whereInCondition = Transaction::$transactionTypes;

        $transaction = Transaction::with([
            'currency:id,type,code,symbol',
            'user:id,first_name,last_name,picture,email,formattedPhone',
            'end_user:id,first_name,last_name,picture,email,formattedPhone',
            'payment_method:id,name',
            'transaction_type:id,name',
            'merchant:id,business_name',
        ])
        ->where($conditions)
        ->whereIn('transactions.transaction_type_id', $whereInCondition)
        ->orderBy('transactions.id', 'desc')
        ->select([
            'transactions.id as id',
            'transactions.user_id',
            'transactions.end_user_id',
            'transactions.currency_id',
            'transactions.payment_method_id',
            'transactions.merchant_id as merchant_id',
            'transactions.transaction_type_id',
            'transactions.transaction_reference_id as transaction_reference_id',
            'transactions.charge_percentage as charge_percentage',
            'transactions.charge_fixed as charge_fixed',
            'transactions.subtotal as subtotal',
            'transactions.total as total',
            'transactions.uuid as transaction_id',
            'transactions.status as status',
            'transactions.note as description',
            'transactions.email as email',
            'transactions.phone as phone',
            'transactions.created_at as t_created_at',
        ])->first();
        if (!$transaction) {
            throw new TransactionException(__("The :x does not exist.", ['x' => __("Transaction")]));
        }
        return new TransactionDetailResource($transaction);
    }
}
