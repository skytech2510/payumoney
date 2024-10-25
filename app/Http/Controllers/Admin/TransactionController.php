<?php

namespace App\Http\Controllers\Admin;

use App\Services\Mail\TransactionUpdatedByAdminMailService;
use App\DataTables\Admin\TransactionsDataTable;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Session, DB, Common, Exception;
use Illuminate\Http\Request;
use App\Models\{User,
    CurrencyExchange,
    MerchantPayment,
    TransactionType,
    RequestPayment,
    PaymentMethod,
    Transaction,
    Withdrawal,
    Currency,
    Transfer,
    Dispute,
    Deposit,
    Wallet
};
use App\Services\Sms\TransactionUpdatedByAdminSmsService;

class TransactionController extends Controller
{
    protected $helper;
    protected $email;
    protected $transaction;

    public function __construct()
    {
        $this->helper      = new Common();
        $this->email       = new EmailController();
        $this->transaction = new Transaction();
    }

    public function index(TransactionsDataTable $dataTable)
    {
        $data = [
            'menu' => 'transaction',
            'sub_menu' => 'transactions',
            'statuses' => [],
            'currencies' => [],
            'transactionTypes' => []
        ];

        $results = Transaction::distinct()->get(['status', 'currency_id', 'transaction_type_id']);
        if (! $results->isEmpty()) {
            foreach ($results as $value) {
                $data['statuses'][$value->status] = $value->status;
                $data['currency_id'][$value->currency_id] = $value->currency_id;
                $data['transaction_type_id'][$value->transaction_type_id] = $value->transaction_type_id;
            }
            $data['currencies'] = Currency::select(['id', 'code'])->whereIn('id', $data['currency_id'])->get();
            $data['transactionTypes'] = TransactionType::select(['id', 'name'])->whereIn('id', $data['transaction_type_id'])->get();
        }

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['type']     = isset(request()->type) ? request()->type : 'all';
        $data['user']     = $user = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->transaction->getTransactionsUsersEndUsersName($user, null);

        return $dataTable->render('admin.transactions.index', $data);
    }

    public function transactionCsv()
    {
        return Excel::download(new TransactionsExport(), 'transaction_list_' . time() . '.xlsx');
    }

    public function transactionPdf()
    {
        $from   = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to     = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $type   = isset(request()->type) ? request()->type : null;
        $user   = isset(request()->user_id) ? request()->user_id : null;
        $currency = isset(request()->currency) ? request()->currency : null;

        $data['transactions'] = $this->transaction->getTransactionsList($from, $to, $status, $currency, $type, $user)->orderBy('transactions.id', 'desc')->take(1100)->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.transactions.transactions_report_pdf', 'transactions_report_', $data);
    }

    public function transactionsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->transaction->getTransactionsUsersResponse($search, null);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0)
        {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function edit($id)
    {
        $data['menu'] = 'transaction';
        $data['sub_menu'] = 'transactions';

        $relation = [
           'user:id,first_name,last_name',
           'end_user:id,first_name,last_name',
           'currency:id,type,code,symbol',
           'merchant_payment:id,gateway_reference,order_no,item_name,fee_bearer',
           'payment_method:id,name',
           'transaction_type:id,name',
           'bank:id,bank_name,bank_branch_name,account_name',
           'file:id,filename,originalname',
           'withdrawal.withdrawal_detail:id,withdrawal_id,account_name,account_number,swift_code,bank_name,email,crypto_address' . (config('mobilemoney.is_active') ? ',mobilemoney_id,mobile_number' : '')
        ];

        $data['transaction'] = $transaction = Transaction::with($relation)->find($id);

        $modules = getAllModules();
        foreach ($modules as $module) {
            if (!empty(config($module->get('alias') . '.transaction_types'))) {
                if (in_array($transaction?->transaction_type->id, config($module->get('alias') . '.transaction_types'))) {

                    $moduleName = $module->get('name');
                    $moduleTransaction = file_exists(("\Modules\\". $moduleName . "\Entities\\" . ucfirst($moduleName) . "Transaction")) . '.php' ? (new ("\Modules\\". $moduleName . "\Entities\\" . ucfirst($moduleName) . "Transaction")($relation)) : '';
                    $data = $moduleTransaction->getTransactionDetails($id);

                    return view($module->get('alias') . '::admin.transaction.edit', $data);
                }
            }
        }

        $data['transactionOfRefunded'] = Transaction::where(['uuid' => $transaction->refund_reference, 'transaction_type_id' => $transaction->transaction_type_id])->first(['id']);
        $data['dispute'] = Dispute::where(['transaction_id' => $id])->select('status')->latest()->first(['status']);
        return view('admin.transactions.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $t                             = Transaction::find($request->id);
        $transferred_row               = Transaction::where(['transaction_type_id' => Transferred, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])->first();
        $exchange_from                 = Transaction::where(['transaction_type_id' => Exchange_From, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])->first();
        $requestToTypeTransactionEntry = Transaction::where(['transaction_type_id' => Request_Received, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])
            ->select('percentage', 'charge_percentage', 'charge_fixed')->first();
        $userInfo         = User::where(['id' => trim($request->user_id)])->first();
        $getEndUser       = User::where(['id' => trim($request->end_user_id)])->first();
        $getPaymentMethod = PaymentMethod::where(['id' => base64_decode($request->payment_method_id)])->first(['name']);

        // Deposit
        if ($request->transaction_type_id == Deposit) {

            try {
                DB::beginTransaction();

                if ($request->status == 'Pending') {

                    if ($t->status == 'Pending') {
                        $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
                        return redirect(config('adminPrefix') . '/transactions');
                    } elseif ($t->status == 'Success') {
                        Deposit::updateStatus($request->transaction_reference_id, $request->status);
                        $this->transaction->updateTransactionStatus($request->id, $request->status);
                        Wallet::deductAmountFromWallet($request->user_id, $request->currency_id, $request->subtotal);
                    } elseif ($t->status == 'Blocked') {
                        Deposit::updateStatus($request->transaction_reference_id, $request->status);
                        $this->transaction->updateTransactionStatus($request->id, $request->status);
                    }

                } elseif ($request->status == 'Blocked') {

                    if ($t->status == 'Blocked') {
                        $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                        return redirect(config('adminPrefix') . '/transactions');
                    } elseif ($t->status == 'Pending') {
                        Deposit::updateStatus($request->transaction_reference_id, $request->status);
                        $this->transaction->updateTransactionStatus($request->id, $request->status);
                    } elseif ($t->status == 'Success') {
                        Deposit::updateStatus($request->transaction_reference_id, $request->status);
                        $this->transaction->updateTransactionStatus($request->id, $request->status);
                        Wallet::deductAmountFromWallet($request->user_id, $request->currency_id, $request->subtotal);
                    }

                } elseif ($request->status == 'Success') {

                    if ($t->status == 'Success') {
                        $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                        return redirect(config('adminPrefix') . '/transactions');
                    } elseif ($t->status == 'Blocked') {
                        Deposit::updateStatus($request->transaction_reference_id, $request->status);
                        $transaction = $this->transaction->updateTransactionStatus($request->id, $request->status);
                        Wallet::incrementWalletBalance($request->user_id, $request->currency_id, $request->subtotal);
                        if (module('Referral') && settings('referral_enabled') == 'Yes') {
                            $currency   = Currency::find($transaction['currency_id'], ['code']);
                            $refAwardData = [
                                'userId'          => $transaction['user_id'],
                                'currencyId'      => $transaction['currency_id'],
                                'currencyCode'    => $currency->code,
                                'presentAmount'   => $transaction['subtotal'],
                                'paymentMethodId' => Mts,
                                'transactionType' => 'Deposit',
                            ];
                            $awardResponse = (new \Modules\Referral\Entities\ReferralAward)->checkReferralAward($refAwardData);
                        }
                    } elseif ($t->status == 'Pending') {
                        Deposit::updateStatus($request->transaction_reference_id, $request->status);
                        $transaction = $this->transaction->updateTransactionStatus($request->id, $request->status);
                        Wallet::incrementWalletBalance($request->user_id, $request->currency_id, $request->subtotal);
                        if (module('Referral') && settings('referral_enabled') == 'Yes') {
                            $currency   = Currency::find($transaction['currency_id'], ['code']);
                            $refAwardData = [
                                'userId'          => $transaction['user_id'],
                                'currencyId'      => $transaction['currency_id'],
                                'currencyCode'    => $currency->code,
                                'presentAmount'   => $transaction['subtotal'],
                                'paymentMethodId' => Mts,
                                'transactionType' => 'Deposit',
                            ];
                            $awardResponse = (new \Modules\Referral\Entities\ReferralAward)->checkReferralAward($refAwardData);
                        }
                    }
                    
                } 

                DB::commit();

                if (module('Referral') && settings('referral_enabled') == 'Yes' && !empty($awardResponse)) {
                    if (isset($awardResponse['email_status']) && $awardResponse['email_status'] === 200 && !empty($awardResponse['email_details'])) {
                        $awardInfo = (new \Modules\Referral\Services\Email\ReferralAwardMailService)->send($awardResponse['email_details']);
                        \Modules\Referral\Jobs\ProcessRewardEmail::dispatch($awardInfo);
                    }
                }
                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                return redirect(config('adminPrefix').'/transactions');

            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('success', $e->getMessage());
                return redirect(config('adminPrefix').'/transactions');
            }
        }

        if ($request->transaction_type_id == Withdrawal) {
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Blocked') {
                    $withdrawal = Withdrawal::updateStatus($request->transaction_reference_id, $request->status);
                    $this->transaction->updateTransactionStatus($request->id, $request->status);
                    Wallet::deductAmountFromWallet($request->user_id, $request->currency_id, trim($request->total, '-'));

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal?->user?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $withdrawal = Withdrawal::updateStatus($request->transaction_reference_id, $request->status);
                    $this->transaction->updateTransactionStatus($request->id, $request->status);

                    $data = [
                        'amount' => 'No amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal?->user?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $withdrawal = Withdrawal::updateStatus($request->transaction_reference_id, $request->status);
                    $this->transaction->updateTransactionStatus($request->id, $request->status);
                    
                    $data = [
                        'amount' => 'No amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal?->user?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Blocked') {
                    $withdrawal = Withdrawal::updateStatus($request->transaction_reference_id, $request->status);
                    $this->transaction->updateTransactionStatus($request->id, $request->status);
                    Wallet::deductAmountFromWallet($request->user_id, $request->currency_id, trim($request->total, '-'));

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal?->user?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('blocked')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $withdrawal = Withdrawal::updateStatus($request->transaction_reference_id, $request->status);
                    $this->transaction->updateTransactionStatus($request->id, $request->status);
                    Wallet::incrementWalletBalance($request->user_id, $request->currency_id, trim($request->total, '-'));

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal?->user?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Success') {
                    $withdrawal = Withdrawal::updateStatus($request->transaction_reference_id, $request->status);
                    $this->transaction->updateTransactionStatus($request->id, $request->status);
                    Wallet::incrementWalletBalance($request->user_id, $request->currency_id, trim($request->total, '-'));

                    $data = [
                        'amount' => moneyFormat(optional($withdrawal->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $withdrawal->user,
                        'type' => 'Withdrawal'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($withdrawal, $data);

                    if (!empty($withdrawal?->user?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($withdrawal, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        //Transferred
        if ($request->transaction_type_id == Transferred) {
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $transfers = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank)) {
                        //Transferred entry update
                        $transaction = Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance + trim($request->total, '-'),
                        ]);

                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->sender,
                            'type' => 'Transferred'
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                        if (!empty($transfers?->sender?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    } else {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        if (isset($transfers->receiver)) {
                            //add amount to receiver wallet only
                            $receiver_wallet = Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->select('balance')->first();

                            Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->update([
                                'balance' => $receiver_wallet->balance + $request->subtotal,
                            ]);

                            $data = [
                                'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                                'action' => 'added',
                                'fromTo' => 'to',
                                'user' => $transfers->receiver,
                                'type' => 'Transferred'
                            ];
                            
                            (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                            if (!empty($transfers?->receiver?->formattedPhone)) {
                                (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Blocked') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - trim($request->total, '-'),
                    ]);

                    if (isset($transfers->receiver)) {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->subtotal,
                        ]);
                    }

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Transferred'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Transferred'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank)) {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance - trim($request->total, '-'),
                        ]);


                        // Notification send
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                            'user' => $transfers->sender,
                            'type' => 'Transferred'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                        if (!empty($transfers?->sender?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    } else {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        if (isset($transfers->receiver)) {
                            //deduct amount from receiver wallet only
                            $receiver_wallet = Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->select('balance')->first();

                            Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->update([
                                'balance' => $receiver_wallet->balance - $request->subtotal,
                            ]);
                        }

                        if (isset($transfers->receiver)) {
                            $data = [
                                'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                                'action' => 'subtracted',
                                'fromTo' => 'from',
                                'user' => $transfers->receiver,
                                'type' => 'Transferred'
                            ];
                            
                            (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                            if (!empty($transfers?->receiver?->formattedPhone)) {
                                (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Blocked') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - trim($request->total, '-'),
                    ]);

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Transferred'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Transferred'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Transferred'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Pending') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Transferred'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Refund') {
                if ($t->status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $unique_code            = unique_code();
                    $transfers              = new Transfer();
                    $transfers->sender_id   = $request->user_id;
                    $transfers->receiver_id = $request->end_user_id;
                    $transfers->currency_id = $request->currency_id;
                    $transfers->uuid        = $unique_code;
                    $transfers->fee         = $request->charge_percentage + $request->charge_fixed;
                    $transfers->amount      = $request->subtotal;
                    $transfers->note        = $t->transfer->note;
                    $transfers->email       = $t->transfer->email;
                    $transfers->status      = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Transferred entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->user_id;
                    $refund_t_A->end_user_id              = $request->end_user_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $transfers->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Transferred
                    $refund_t_A->user_type                = $t->user_type;
                    $refund_t_A->email                    = $t->transfer->email;
                    $refund_t_A->subtotal                 = $request->subtotal;
                    $refund_t_A->percentage               = $request->percentage;
                    $refund_t_A->charge_percentage        = $request->charge_percentage;
                    $refund_t_A->charge_fixed             = $request->charge_fixed;
                    $refund_t_A->total                    = $request->charge_percentage + $request->charge_fixed + $request->subtotal;
                    $refund_t_A->note                     = $t->transfer->note;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->end_user_id;
                    $refund_t_B->end_user_id              = $request->user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $transfers->id;
                    $refund_t_B->transaction_type_id      = Received; //Received
                    $refund_t_B->user_type                = $t->user_type;
                    $refund_t_B->email                    = $t->transfer->email;
                    $refund_t_B->subtotal                 = $request->subtotal;
                    $refund_t_B->total                    = '-' . $request->subtotal;
                    $refund_t_B->note                     = $t->transfer->note;
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    if (isset($transfers->receiver)) {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Transferred'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Transferred'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        //Received
        if ($request->transaction_type_id == Received) {
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    if (isset($transfers->receiver)) {
                        //add amount to receiver wallet only
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->subtotal,
                        ]);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Received'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Blocked') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->total + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    if (isset($transfers->receiver)) {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->subtotal,
                        ]);
                    }


                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Received'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Received'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    if (isset($transfers->receiver)) {
                        //deduct amount from receiver wallet only
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Received'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Blocked') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->total + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Received'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($request->total + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    if (isset($transfers->receiver)) {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }
                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Received'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Received'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                } elseif ($t->status == 'Pending') {
                    $transfers         = Transfer::with('transaction')->find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + (trim($request->total, '-') + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Received'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Refund') {
                if ($t->status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $unique_code = unique_code();

                    $transfers              = new Transfer();
                    $transfers->sender_id   = $request->end_user_id;
                    $transfers->receiver_id = $request->user_id;
                    $transfers->currency_id = $request->currency_id;
                    $transfers->uuid        = $unique_code;
                    $transfers->fee         = $transferred_row->charge_percentage + $transferred_row->charge_fixed;
                    $transfers->amount      = $request->subtotal;
                    $transfers->note        = $t->transfer->note;
                    $transfers->email       = $t->transfer->email;
                    $transfers->status      = $request->status;
                    $transfers->save();

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Transferred entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->end_user_id;
                    $refund_t_A->end_user_id              = $request->user_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $transfers->id;
                    $refund_t_A->transaction_type_id      = Transferred; //Transferred
                    $refund_t_A->user_type                = $t->user_type;
                    $refund_t_A->email                    = $t->transfer->email;
                    $refund_t_A->subtotal                 = $request->subtotal;
                    $refund_t_A->percentage               = $transferred_row->percentage;
                    $refund_t_A->charge_percentage        = $transferred_row->charge_percentage;
                    $refund_t_A->charge_fixed             = $transferred_row->charge_fixed;
                    $refund_t_A->total                    = $transferred_row->charge_percentage + $transferred_row->charge_fixed + $refund_t_A->subtotal;
                    $refund_t_A->note                     = $t->transfer->note;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->user_id;
                    $refund_t_B->end_user_id              = $request->end_user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $transfers->id;
                    $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Received
                    $refund_t_B->user_type                = $t->user_type;
                    $refund_t_B->email                    = $t->transfer->email;
                    $refund_t_B->subtotal                 = $request->subtotal;
                    $refund_t_B->total                    = '-' . $request->subtotal;
                    $refund_t_B->note                     = $t->transfer->note;
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($transferred_row->charge_percentage + $transferred_row->charge_fixed + $refund_t_A->subtotal),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber(trim($request->total, '-'))),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $transfers->sender,
                        'type' => 'Received'
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($transfers, $data);

                    if (!empty($transfers?->sender?->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                    }

                    if (isset($transfers->receiver)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $transfers->receiver,
                            'type' => 'Received'
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($transfers, $data);
    
                        if (!empty($transfers?->receiver?->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        //Exchange_From
        if ($request->transaction_type_id == Exchange_From) {
            $exFromSubtotal = number_format((float) $request->subtotal, 2, '.', '');
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Blocked') {
                    $exchange         = CurrencyExchange::with('transaction')->find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - trim($request->total, '-'),
                    ]);

                    //receiver wallet entry update
                    $receiver_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        // 'balance' => $receiver_wallet->balance + trim($request->total, '-') * $exchange->exchange_rate,
                        'balance' => $receiver_wallet->balance + ($exFromSubtotal * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $exchange = CurrencyExchange::with('transaction')->find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    //receiver wallet entry update
                    $receiver_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        // 'balance' => $receiver_wallet->balance - (trim($request->total, '-') * $exchange->exchange_rate),
                        'balance' => $receiver_wallet->balance - ($exFromSubtotal * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        //Exchange_To
        if ($request->transaction_type_id == Exchange_To) {
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Blocked') {
                    $exchange         = CurrencyExchange::with('transaction')->find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_From,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //receiver wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $to_wallet->balance + $request->total,
                    ]);

                    //sender wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance - trim($exchange_from->total, '-'),
                    ]);

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $exchange         = CurrencyExchange::with('transaction')->find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_From,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //receiver wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $to_wallet->balance - $request->total,
                    ]);

                    //sender wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance + trim($exchange_from->total, '-'),
                    ]);

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        $getPaymentReceivedMerchantTransaction = Transaction::where(['transaction_type_id' => Payment_Received, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])
            ->first();

        if ($request->transaction_type_id == Payment_Sent) {
            if ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $merchant_payment         = MerchantPayment::with('transaction')->find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage),
                    ]);


                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage)),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $merchant_payment?->merchant?->user,
                        'type' => 'Payment_Sent',
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $merchant_payment         = MerchantPayment::with('transaction')->find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage),
                    ]);

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage)),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $merchant_payment?->merchant?->user,
                        'type' => 'Payment_Sent',
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Refund') {
                if ($t->status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $unique_code = unique_code();
                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = $request->user_id;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $getPaymentReceivedMerchantTransaction->charge_percentage;
                    $merchant_payment->charge_fixed = $getPaymentReceivedMerchantTransaction->charge_fixed;
                    $merchant_payment->amount = $request->subtotal - ($getPaymentReceivedMerchantTransaction->charge_percentage + $getPaymentReceivedMerchantTransaction->charge_fixed);
                    $merchant_payment->total  = '-' . $request->subtotal;
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    //Payment_Sent old entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Payment_Sent entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->user_id;
                    $refund_t_A->end_user_id              = $request->end_user_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_A->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $request->transaction_reference_id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Payment_Sent
                    $refund_t_A->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_A->subtotal                 = $request->subtotal;
                    $refund_t_A->percentage               = $request->percentage;
                    $refund_t_A->charge_percentage        = 0;
                    $refund_t_A->charge_fixed             = 0;
                    $refund_t_A->total                    = $request->subtotal;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Payment_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->end_user_id;
                    $refund_t_B->end_user_id              = $request->user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $request->transaction_reference_id;
                    $refund_t_B->transaction_type_id      = Payment_Received; //Payment_Received
                    $refund_t_B->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_B->subtotal          = $request->subtotal - ($getPaymentReceivedMerchantTransaction->charge_percentage + $getPaymentReceivedMerchantTransaction->charge_fixed);
                    $refund_t_B->percentage        = $request->percentage;
                    $refund_t_B->charge_percentage = $getPaymentReceivedMerchantTransaction->charge_percentage;
                    $refund_t_B->charge_fixed = $getPaymentReceivedMerchantTransaction->charge_fixed;
                    $refund_t_B->total        = '-' . $request->subtotal;
                    $refund_t_B->status       = $request->status;
                    $refund_t_B->save();

                    //add amount to paid_by_user wallet
                    if (isset($merchant_payment->user_id))
                    {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $merchant_payment->user->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $merchant_payment->user->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $paid_by_user->balance + $request->subtotal,
                        ]);
                    }

                    // Notification send
                    $data = [
                        'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, ($request->subtotal)),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $merchant_payment?->user,
                        'type' => 'Payment_Sent',
                    ];
                    
                    (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                    }

                    //deduct amount from merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $merchant_payment->amount,
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data = [
                            'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($merchant_payment->amount)),
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                            'user' => $merchant_payment->merchant->user,
                            'type' => 'Payment_Sent',
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        // Payment_Received
        if ($request->transaction_type_id == Payment_Received) {
            if ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $merchant_payment         = MerchantPayment::with('transaction')->find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    if ($getPaymentMethod->name != 'Mts') {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);
                    } else {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($getEndUser) ? $getEndUser->id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        Transaction::where([
                            'user_id'                  => isset($getEndUser) ? $getEndUser->id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Payment_Sent,
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->subtotal,
                    ]);

                    //Sender(user_id)
                    if (isset($merchant_payment->merchant)) {
                        $data = [
                            'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                            'user' => $merchant_payment->merchant->user,
                            'type' => 'Payment_Sent',
                        ];
                        
                        (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $merchant_payment         = MerchantPayment::with('transaction')->find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    if ($getPaymentMethod->name != 'Mts') {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'status' => $request->status,
                        ]);
                    } else {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($getEndUser) ? $getEndUser->id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'status' => $request->status,
                        ]);

                        Transaction::where([
                            'user_id'                  => isset($getEndUser) ? $getEndUser->id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Payment_Sent,
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + $request->subtotal,
                    ]);

                    if (isset($merchant_payment->merchant)) {
                        $data = [
                            'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $merchant_payment->merchant->user,
                            'type' => 'Payment_Sent',
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Refund') {
                if ($t->status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('merchant payment'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $unique_code = unique_code();
                    //MerchantPayment
                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = isset($getEndUser) ? $getEndUser->id : null;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $request->charge_percentage;
                    $merchant_payment->charge_fixed = $request->charge_fixed;
                    $merchant_payment->amount       = $request->subtotal;
                    $merchant_payment->total  = '-' . ($request->charge_percentage + $request->charge_fixed + $request->subtotal);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    //update refund reference
                    if ($getPaymentMethod->name != 'Mts') {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'refund_reference' => $unique_code,
                        ]);
                    } else {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($getEndUser) ? $getEndUser->id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'refund_reference' => $unique_code,
                        ]);

                        Transaction::where([
                            'user_id'                  => isset($getEndUser) ? $getEndUser->id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Payment_Sent,
                        ])->update([
                            'refund_reference' => $unique_code,
                        ]);
                    }

                    if ($getPaymentMethod->name != 'Mts') {
                        $refund_t_B              = new Transaction();
                        $refund_t_B->user_id     = $request->user_id;
                        $refund_t_B->end_user_id = null;

                        $refund_t_B->currency_id              = $request->currency_id;
                        $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                        $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                        $refund_t_B->uuid                     = $unique_code;
                        $refund_t_B->refund_reference         = $request->uuid;
                        $refund_t_B->transaction_reference_id = $request->transaction_reference_id;
                        $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Payment_Received
                        $refund_t_B->subtotal                 = $request->subtotal;
                        $refund_t_B->percentage               = $request->percentage;
                        $refund_t_B->charge_percentage        = $request->charge_percentage;
                        $refund_t_B->charge_fixed             = $request->charge_fixed;
                        $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->subtotal);
                        $refund_t_B->status                   = $request->status;
                        $refund_t_B->save();
                    } else {
                        $refund_t_A                           = new Transaction();
                        $refund_t_A->user_id                  = $request->end_user_id;
                        $refund_t_A->end_user_id              = $request->user_id;
                        $refund_t_A->currency_id              = $request->currency_id;
                        $refund_t_A->payment_method_id        = base64_decode($request->payment_method_id);
                        $refund_t_A->merchant_id              = base64_decode($request->merchant_id);
                        $refund_t_A->uuid                     = $unique_code;
                        $refund_t_A->refund_reference         = $request->uuid;
                        $refund_t_A->transaction_reference_id = $request->transaction_reference_id;
                        $refund_t_A->transaction_type_id      = Payment_Sent; //Payment_Sent
                        $refund_t_A->subtotal                 = $request->total;
                        $refund_t_A->percentage               = $request->percentage;
                        $refund_t_A->charge_percentage        = 0;
                        $refund_t_A->charge_fixed             = 0;
                        $refund_t_A->total                    = $request->total;
                        $refund_t_A->status                   = $request->status;
                        $refund_t_A->save();

                        //New Payment_Received entry
                        $refund_t_B                           = new Transaction();
                        $refund_t_B->user_id                  = $request->user_id;
                        $refund_t_B->end_user_id              = $request->end_user_id;
                        $refund_t_B->currency_id              = $request->currency_id;
                        $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                        $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                        $refund_t_B->uuid                     = $unique_code;
                        $refund_t_B->refund_reference         = $request->uuid;
                        $refund_t_B->transaction_reference_id = $request->transaction_reference_id;
                        $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Payment_Received
                        $refund_t_B->subtotal                 = $request->subtotal;
                        $refund_t_B->percentage               = $request->percentage;
                        $refund_t_B->charge_percentage        = $request->charge_percentage;
                        $refund_t_B->charge_fixed             = $request->charge_fixed;
                        $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->subtotal);
                        $refund_t_B->status                   = $request->status;
                        $refund_t_B->save();
                    }

                    //add amount to paid_by_user wallet, if exists
                    if (isset($merchant_payment->user_id)) {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $merchant_payment->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $merchant_payment->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            // 'balance' => $paid_by_user->balance + $request->total,
                            'balance' => $paid_by_user->balance + ($request->charge_percentage + $request->charge_fixed + $request->subtotal),
                        ]);
                    }

                    //deduct amount from merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->subtotal,
                    ]);

                    //Sender(end_user_id) //paid_by_user
                    if (isset($merchant_payment->user_id)) {
                        $data = [
                            'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->total)),
                            'action' => 'added',
                            'fromTo' => 'to',
                            'user' => $merchant_payment->user,
                            'type' => 'Payment_Sent',
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                        }
                    }

                    if (isset($merchant_payment->merchant)) {
                        $data = [
                            'amount' => moneyFormat(optional($merchant_payment->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                            'user' => $merchant_payment->merchant->user,
                            'type' => 'Payment_Sent',
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($merchant_payment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($merchant_payment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('merchant payment')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        if ($request->transaction_type_id == Request_Sent) {
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                }
            } elseif ($request->status == 'Refund') {
                if ($t->status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $unique_code = unique_code();
                    $requestpayment = new RequestPayment();
                    $requestpayment->user_id = $request->user_id;
                    $requestpayment->receiver_id = $request->end_user_id;
                    $requestpayment->currency_id = $request->currency_id;
                    $requestpayment->uuid = $unique_code;
                    $requestpayment->amount = $t->request_payment->amount;
                    $requestpayment->accept_amount = $request->subtotal;
                    $requestpayment->email = $t->request_payment->email;
                    $requestpayment->note = $t->request_payment->note;
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Request_Sent entry
                    $refund_t_A = new Transaction();
                    $refund_t_A->user_id     = $request->user_id;
                    $refund_t_A->end_user_id = $request->end_user_id;
                    $refund_t_A->currency_id = $request->currency_id;
                    $refund_t_A->uuid = $unique_code;
                    $refund_t_A->refund_reference = $request->uuid;
                    $refund_t_A->transaction_reference_id = $requestpayment->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id;
                    $refund_t_A->user_type = $t->user_type;
                    $refund_t_A->subtotal = $request->subtotal;
                    $refund_t_A->total = '-' . $refund_t_A->subtotal;
                    $refund_t_A->note   = $t->request_payment->note;
                    $refund_t_A->status = $request->status;
                    $refund_t_A->save();

                    //New Request_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->end_user_id;
                    $refund_t_B->end_user_id              = $request->user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $requestpayment->id;
                    $refund_t_B->transaction_type_id      = Request_Received;
                    $refund_t_B->user_type = $t->user_type;
                    $refund_t_B->subtotal = $request->subtotal;
                    $refund_t_B->percentage        = $requestToTypeTransactionEntry->percentage;
                    $refund_t_B->charge_percentage = $requestToTypeTransactionEntry->charge_percentage;
                    $refund_t_B->charge_fixed      = $requestToTypeTransactionEntry->charge_fixed;
                    $refund_t_B->total = ($requestToTypeTransactionEntry->charge_percentage + $requestToTypeTransactionEntry->charge_fixed + $refund_t_B->subtotal);
                    $refund_t_B->note = $t->request_payment->note;
                    $refund_t_B->status = $request->status;
                    $refund_t_B->save();

                    //sender wallet entry update
                    $request_created_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $request_created_wallet->balance - $request->subtotal,
                    ]);

                    if (isset($request->end_user_id)) {
                        $request_accepted_wallet = Wallet::where([
                            'user_id'     => $request->end_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->end_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $request_accepted_wallet->balance + $refund_t_B->total,
                        ]);
                    }

                    $data = [
                        'amount' => moneyFormat(optional($requestpayment->currency)->symbol, formatNumber($request->subtotal)),
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                        'user' => $requestpayment->user,
                        'type' => 'Request_Sent'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                    }

                    if (isset($request->end_user_id)) {
                        $data = [
                            'amount' => moneyFormat(optional($requestpayment->currency)->symbol, formatNumber($refund_t_B->total)),
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                            'user' => $requestpayment->receiver,
                            'type' => 'Request_Sent'
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $requestpayment         = RequestPayment::with('transaction')->find($request->transaction_reference_id);
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    $transaction_creator = Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $transaction_acceptor = Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No Amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from/to',
                        'user' => $requestpayment->user,
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                    }

                    if (isset($requestpayment->receiver)) {
                        $data = [
                            'amount' => 'No Amount',
                            'action' => 'added/subtracted',
                            'fromTo' => 'from/to',
                            'user' => $requestpayment->receiver,
                            'type' => 'Request_Sent'
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Blocked') {
                    $request_payment         = RequestPayment::with('transaction')->find($request->transaction_reference_id);
                    $request_payment->status = $request->status;
                    $request_payment->save();

                    //Request From entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Request To entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No Amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from/to',
                        'user' => $request_payment->user,
                        'type' => 'Request_Sent'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($request_payment->transaction, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($request_payment->transaction, $data);
                    }

                    if (isset($request_payment->receiver)) {
                        $data = [
                            'amount' => 'No Amount',
                            'action' => 'added/subtracted',
                            'fromTo' => 'from/to',
                            'user' => $request_payment->receiver,
                            'type' => 'Request_Sent'
                        ];

                        (new TransactionUpdatedByAdminMailService)->send($request_payment->transaction, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($request_payment->transaction, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        //Request_Received
        if ($request->transaction_type_id == Request_Received) {
            if ($request->status == 'Success') {
                if ($t->status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transactions');
                }
            } elseif ($request->status == 'Refund') {
                if ($t->status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Success') {
                    $unique_code = unique_code();
                    $requestpayment = new RequestPayment();
                    $requestpayment->user_id     = $request->end_user_id;
                    $requestpayment->receiver_id = $request->user_id;
                    $requestpayment->currency_id = $request->currency_id;
                    $requestpayment->uuid = $unique_code;
                    $requestpayment->amount        = $t->request_payment->amount;
                    $requestpayment->accept_amount = $request->subtotal;
                    $requestpayment->email = $t->request_payment->email;
                    $requestpayment->note = $t->request_payment->note;
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    //Request_Sent entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Sent,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Request_Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Request_Sent entry
                    $refund_t_A = new Transaction();
                    $refund_t_A->user_id     = $request->end_user_id;
                    $refund_t_A->end_user_id = $request->user_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $requestpayment->id;
                    $refund_t_A->transaction_type_id = Request_Sent; //Request_Sent
                    $refund_t_A->user_type = $t->user_type;
                    $refund_t_A->subtotal = $request->subtotal;
                    $refund_t_A->total    = '-' . $refund_t_A->subtotal;
                    $refund_t_A->note = $t->request_payment->note;
                    $refund_t_A->status = $request->status;
                    $refund_t_A->save();

                    //New Request_Received entry
                    $refund_t_B = new Transaction();
                    $refund_t_B->user_id     = $request->user_id;
                    $refund_t_B->end_user_id = $request->end_user_id;
                    $refund_t_B->currency_id = $request->currency_id;
                    $refund_t_B->uuid = $unique_code;
                    $refund_t_B->refund_reference = $request->uuid;
                    $refund_t_B->transaction_reference_id = $requestpayment->id;
                    $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Request_Received
                    $refund_t_B->user_type = $t->user_type;
                    $refund_t_B->subtotal          = $request->subtotal;
                    $refund_t_B->percentage        = $request->percentage;
                    $refund_t_B->charge_percentage = $request->charge_percentage;
                    $refund_t_B->charge_fixed      = $request->charge_fixed;
                    $refund_t_B->total             = ($request->charge_percentage + $request->charge_fixed + $refund_t_B->subtotal);
                    $refund_t_B->note   = $t->request_payment->note;
                    $refund_t_B->status = $request->status;
                    $refund_t_B->save();

                    Wallet::incrementWalletBalance($request->user_id, $request->currency_id, $refund_t_B->total);

                    if (isset($request->end_user_id)) {
                        Wallet::deductAmountFromWallet($request->end_user_id, $request->currency_id, $request->subtotal);
                    }

                    $data = [
                        'amount' => moneyFormat(optional($requestpayment->currency)->symbol, formatNumber($refund_t_B->total)),
                        'action' => 'added',
                        'fromTo' => 'to',
                        'user' => $requestpayment->receiver,
                        'type' => 'Request_Received'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                    }

                    if (isset($request->end_user_id)) {
                        $data = [
                            'amount' => moneyFormat(optional($requestpayment->currency)->symbol, formatNumber($request->subtotal)),
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                            'user' => $requestpayment->user,
                            'type' => 'Request_Received'
                        ];
    
                        (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Blocked') {
                if ($t->status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('canceled')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Pending') {
                    $requestpayment         = RequestPayment::with('transaction')->find($request->transaction_reference_id);
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    $transaction_creator = Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $transaction_acceptor = Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Sent,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No Amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from/to',
                        'user' => $requestpayment->user,
                        'type' => 'Request_Received'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                    }

                    if (isset($requestpayment->receiver)) {
                        $data = [
                            'amount' => 'No Amount',
                            'action' => 'added/subtracted',
                            'fromTo' => 'from/to',
                            'user' => $requestpayment->receiver,
                            'type' => 'Request_Received'
                        ];
    
                        (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            } elseif ($request->status == 'Pending') {
                if ($t->status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix') . '/transactions');
                } elseif ($t->status == 'Blocked') {
                    $requestpayment         = RequestPayment::with('transaction')->find($request->transaction_reference_id);
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    // Request_Received
                    $transaction_creator = Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Request_Sent
                    $transaction_acceptor = Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_Sent,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No Amount',
                        'action' => 'added/subtracted',
                        'fromTo' => 'from/to',
                        'user' => $requestpayment->user,
                        'type' => 'Request_Received'
                    ];

                    (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                    }

                    if (isset($requestpayment->receiver)) {
                        $data = [
                            'amount' => 'No Amount',
                            'action' => 'added/subtracted',
                            'fromTo' => 'from/to',
                            'user' => $requestpayment->receiver,
                            'type' => 'Request_Received'
                        ];
    
                        (new TransactionUpdatedByAdminMailService)->send($requestpayment, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransactionUpdatedByAdminSmsService)->send($requestpayment, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                    return redirect(config('adminPrefix').'/transactions');
                }
            }
        }

        // Crypto Exchange
        if (module('CryptoExchange') && ($request->transaction_type_id == Crypto_Swap || $request->transaction_type_id == Crypto_Buy || $request->transaction_type_id == Crypto_Sell)) {
            if (!isActive('CryptoExchange')) {
                $this->helper->one_time_message('error',  __('Crypto Exchange module is Inactive'));
                return redirect(config('adminPrefix') . '/transactions');
            }

            if ($request->status == $t->status) {
                $this->helper->one_time_message('success', __('Transaction is already :x', ['x' => $request->status]));
                return redirect(config('adminPrefix') . '/transactions');
            }

            if ($request->status == 'Pending') {
                $this->helper->one_time_message('success', __('Status not changed'));
                return redirect(config('adminPrefix') . '/crypto_exchanges');
            }

            $cryptoExchange = \Modules\CryptoExchange\Entities\CryptoExchange::with('transaction')->find($request->transaction_reference_id);

            $cryptoExchange->status = $request->status;
            $cryptoExchange->save();

            //Transferred entry update
            Transaction::where([
                'uuid'                     => $request->uuid,
                'transaction_reference_id' => $request->transaction_reference_id,
                'transaction_type_id'      => $request->transaction_type_id,
            ])->update([
                'status' => $request->status,
            ]);

            if ($cryptoExchange->user_id !== NULL) {
                $to_wallet = Wallet::where([
                    'user_id'     => $cryptoExchange->user_id,
                    'currency_id' => $cryptoExchange->to_currency,
                ])->select('balance')->first();

                $from_wallet = Wallet::where([
                    'user_id'     => $cryptoExchange->user_id,
                    'currency_id' => $cryptoExchange->from_currency,
                ])->select('balance')->first();


                if ($request->status == 'Success') {
                    if ($t->status == 'Pending') {
                        if ($cryptoExchange->receive_via == 'wallet') {
                            if (!$to_wallet) {
                                $to_wallet = Wallet::createWallet($cryptoExchange->user_id, $cryptoExchange->to_currency);
                            }

                            Wallet::where([
                                'user_id'     => $cryptoExchange->user_id,
                                'currency_id' => $cryptoExchange->to_currency,
                            ])->update([
                                'balance' => $to_wallet->balance + $cryptoExchange->get_amount,
                            ]);
                        }
                    } elseif ($t->status == 'Blocked') {
                        if ($cryptoExchange->receive_via == 'wallet') {
                            Wallet::where([
                                'user_id'     => $cryptoExchange->user_id,
                                'currency_id' => $cryptoExchange->to_currency,
                            ])->update([
                                'balance' => $to_wallet->balance + $cryptoExchange->get_amount,
                            ]);
                        }

                        if ($cryptoExchange->send_via == 'wallet') {
                            Wallet::where([
                                'user_id'     => $cryptoExchange->user_id,
                                'currency_id' => $cryptoExchange->from_currency,
                            ])->update([
                                'balance' => $from_wallet->balance - $cryptoExchange->amount,
                            ]);
                        }
                    }
                } elseif ($request->status == 'Blocked') {
                    if ($t->status == 'Success') {
                        if ($cryptoExchange->receive_via == 'wallet') {
                            Wallet::where([
                                'user_id'     => $cryptoExchange->user_id,
                                'currency_id' => $cryptoExchange->to_currency,
                            ])->update([
                                'balance' => $to_wallet->balance - $cryptoExchange->get_amount,
                            ]);
                        }

                        if ($cryptoExchange->send_via == 'wallet') {
                            Wallet::where([
                                'user_id'     => $cryptoExchange->user_id,
                                'currency_id' => $cryptoExchange->from_currency,
                            ])->update([
                                'balance' => $from_wallet->balance + $cryptoExchange->amount,
                            ]);
                        }
                    } elseif ($t->status == 'Pending') {
                        if ($cryptoExchange->send_via == 'wallet') {
                            Wallet::where([
                                'user_id'     => $cryptoExchange->user_id,
                                'currency_id' => $cryptoExchange->from_currency,
                            ])->update([
                                'balance' => $from_wallet->balance + $cryptoExchange->amount,
                            ]);
                        }
                    }
                }
            }

            $user_name = (!is_null($cryptoExchange->user_id)) ? $cryptoExchange->user->first_name . ' ' . $cryptoExchange->user->last_name : '';

            $data = [
                'amount' => moneyFormat(optional($cryptoExchange->toCurrency)->symbol, formatNumber($cryptoExchange->get_amount, $cryptoExchange->to_currency)),
                'action' => 'added',
                'fromTo' => 'to',
                'user' => $cryptoExchange->user,
            ];

            (new TransactionUpdatedByAdminMailService)->send($cryptoExchange->transaction, $data);

            if (!empty($data['user']->formattedPhone)) {
                (new TransactionUpdatedByAdminSmsService)->send($cryptoExchange->transaction, $data);
            }

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
            return redirect(config('adminPrefix') . '/transactions');
        }

        // Referral Award
        if(module('Referral') && $request->transaction_type_id == Referral_Award) {

            if ($request->status == $t->status) {
                $this->helper->one_time_message('success', __('Transaction is already :x', ['x' => $request->status]));
                return redirect(config('adminPrefix') . '/transactions');
            }

            if ($request->status == 'Pending') {
                $this->helper->one_time_message('success', __('Status not changed'));
                return redirect(config('adminPrefix') . '/transactions');
            }

            if ($request->status == 'Blocked' && $t->status == 'Success') {
                $this->helper->one_time_message('success', __('You can not change transaction status for success to canceled.'));
                return redirect(config('adminPrefix') . '/transactions');
            }

            if ($request->status == 'Success') {

                \Modules\Referral\Entities\ReferralAward::updateStatus($request->transaction_reference_id, $request->status);
                $this->transaction->updateTransactionStatus($request->id, $request->status);
                Wallet::incrementWalletBalance($request->user_id, $request->currency_id, $request->subtotal);

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                return redirect(config('adminPrefix').'/transactions');
                
            }

            if ($request->status == 'Blocked') {
                \Modules\Referral\Entities\ReferralAward::updateStatus($request->transaction_reference_id, $request->status);
                $this->transaction->updateTransactionStatus($request->id, $request->status);

                $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
                return redirect(config('adminPrefix').'/transactions');
            }
        }

    }
}
