<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MoneyTransfersDataTable;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Transaction,
    Transfer,
    Wallet,
    User
};
use App\Exports\TransfersExport;
use Illuminate\Support\Facades\Config;
use App\Services\Mail\TransferReceivedStatusChangeMailService;
use App\Services\Sms\TransferReceivedStatusChangeSmsService;

class MoneyTransferController extends Controller
{
    protected $helper;
    protected $email;
    protected $transfer;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->email    = new EmailController();
        $this->transfer = new Transfer();
    }

    public function index(MoneyTransfersDataTable $dataTable)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'transfers';

        $data['transfer_status']     = $this->transfer->select('status')->groupBy('status')->get();
        $data['transfer_currencies'] = $this->transfer->select('currency_id')->groupBy('currency_id')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['user']     = $user    = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->transfer->getTransfersUserName($user);

        return $dataTable->render('admin.transfers.list', $data);
    }

    public function transferCsv()
    {
        return Excel::download(new TransfersExport(), 'transfer_list_'. time() .'.xlsx');
    }

    public function transferPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $data['transfers'] = $this->transfer->getTransfersList($from, $to, $status, $currency, $user)->orderBy('transfers.id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.transfers.transfers_report_pdf', 'transfers_report_', $data);
    }

    public function transfersUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->transfer->getTransfersUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function edit($id)
    {
        $data['menu']     = 'transaction';
        $data['sub_menu'] = 'transfers';
        $data['transfer'] = $transfer = Transfer::find($id);

        $data['transactionOfRefunded'] = $transactionOfRefunded = Transaction::where(['uuid' => $transfer->uuid])->first(['refund_reference']);

        $data['transferOfRefunded'] = Transfer::where(['uuid' => $transactionOfRefunded->refund_reference])->first(['id']);

        $data['transaction'] = Transaction::select('refund_reference', 'transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'charge_percentage', 'charge_fixed')
            ->where(['transaction_reference_id' => $transfer->id, 'status' => $transfer->status])
            ->whereIn('transaction_type_id', [Transferred, Received])
            ->first();

        return view('admin.transfers.edit', $data);
    }

    public function update(Request $request)
    {
        if (!empty(trim($request->email))) {
            $userInfo = User::where(['email' => trim($request->email)])->first();
        } else {
            $userInfo = User::where(['formattedPhone' => $request->phone])->first();
        }

        //using Transferred transaction_type to update both Transferred and Received entries
        if ($request->transaction_type == 'Transferred') {
            if ($request->status == 'Pending') {
                if ($request->transaction_status == 'Pending') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('pending')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Success') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    if (isset($userInfo)) {
                        //Received entry update
                        Transaction::where([
                            'user_id'                  => $userInfo->id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //deduct amount from receiver wallet only
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->amount,
                        ]);

                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount)),
                            'user'   => $userInfo,
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                        ];

                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Refund') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Blocked') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'subtracted',
                        'fromTo' => 'from',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                }
            } elseif ($request->status == 'Success') {
                if ($request->transaction_status == 'Success') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('successful')]));
                    return redirect(config('adminPrefix') . '/transfers');
                } elseif ($request->transaction_status == 'Pending') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    if (isset($userInfo)) {
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->amount,
                        ]);
                    }

                    // Sent Mail when status is 'Success'
                    if (isset($userInfo)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount)),
                            'user'   => $userInfo,
                            'action' => 'added',
                            'fromTo' => 'to',
                        ];

                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Blocked') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'added',
                        'fromTo' => 'to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    //Received entry update
                    if (isset($userInfo)) {
                        Transaction::where([
                            'user_id'                  => $userInfo->id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        $receiver_wallet = Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->amount,
                        ]);

                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount)),
                            'user'   => $userInfo,
                            'action' => 'added',
                            'fromTo' => 'to',
                        ];
    
                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Refund') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->amount + $request->feesTotal),
                    ]);

                    if (isset($userInfo)) {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->amount,
                        ]);
                    }

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'added',
                        'fromTo' => 'to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    if (isset($userInfo)) {
                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount)),
                            'user'   => $userInfo,
                            'action' => 'added',
                            'fromTo' => 'to',
                        ];
    
                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                }
            } elseif ($request->status == 'Blocked') {
                if ($request->transaction_status == 'Blocked') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('blocked')]));
                    return redirect(config('adminPrefix') . '/transfers');
                } elseif ($request->transaction_status == 'Success') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'added',
                        'fromTo' => 'to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    if (isset($userInfo)) {
                        Transaction::where([
                            'user_id'                  => $userInfo->id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->amount,
                        ]);

                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount)),
                            'user'   => $userInfo,
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                        ];
    
                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Pending') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'added',
                        'fromTo' => 'to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    //Received transactions entry update only
                    if (isset($userInfo)) {
                        Transaction::where([
                            'user_id'                  => $userInfo->id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Refund') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No Amount',
                        'user'   => $transfers->sender,
                        'action' => 'added/subtracted',
                        'fromTo' => 'from/to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    if (isset($userInfo)) {
                        $data = [
                            'amount' => 'No Amount',
                            'user'   => $userInfo,
                            'action' => 'added/subtracted',
                            'fromTo' => 'from/to',
                        ];
    
                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                }
            } elseif ($request->status == 'Refund') {
                if ($request->transaction_status == 'Refund') {
                    $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transfer'), 'y' => __('refunded')]));
                    return redirect(config('adminPrefix') . '/transfers');
                } elseif ($request->transaction_status == 'Success') {
                    $unique_code = unique_code();
                    $transfers              = new Transfer();
                    $transfers->sender_id   = $request->sender_id;
                    $transfers->receiver_id = isset($userInfo) ? $userInfo->id : null;
                    $transfers->currency_id = $request->currency_id;
                    $transfers->uuid        = $unique_code;
                    $transfers->fee         = $request->feesTotal;
                    $transfers->amount      = $request->amount;
                    $transfers->note        = $request->note;
                    $transfers->email       = $request->email;
                    $transfers->status      = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->receiver_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Transferred entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $transfers->sender_id;
                    $refund_t_A->end_user_id              = $transfers->receiver_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $transfers->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Transferred
                    $refund_t_A->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_A->email                    = $request->email;
                    $refund_t_A->subtotal                 = $request->amount;
                    $refund_t_A->percentage               = $request->percentage;
                    $refund_t_A->charge_percentage        = $request->charge_percentage;
                    $refund_t_A->charge_fixed             = $request->charge_fixed;
                    $refund_t_A->total                    = $request->charge_percentage + $request->charge_fixed + $request->amount;
                    $refund_t_A->note                     = $request->note;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $transfers->receiver_id;
                    $refund_t_B->end_user_id              = $transfers->sender_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $transfers->id;
                    $refund_t_B->transaction_type_id      = Received; //Received
                    $refund_t_B->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_B->email                    = $request->email;
                    $refund_t_B->subtotal                 = $request->amount;
                    $refund_t_B->percentage               = 0;
                    $refund_t_B->charge_percentage        = 0;
                    $refund_t_B->charge_fixed             = 0;
                    $refund_t_B->total                    = '-' . $request->amount;
                    $refund_t_B->note                     = $request->note;
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->sender_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($request->amount + $request->feesTotal),
                    ]);

                    $data = [
                        'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount + $request->feesTotal)),
                        'user'   => $transfers->sender,
                        'action' => 'added',
                        'fromTo' => 'to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    if (isset($userInfo)) {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $userInfo->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->amount,
                        ]);

                        $data = [
                            'amount' => moneyFormat(optional($transfers->currency)->symbol, formatNumber($request->amount)),
                            'user'   => $userInfo,
                            'action' => 'subtracted',
                            'fromTo' => 'from',
                        ];
    
                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }

                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                } elseif ($request->transaction_status == 'Blocked') {
                    $transfers         = Transfer::find($request->id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->sender_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $data = [
                        'amount' => 'No Amount',
                        'user'   => $transfers->sender,
                        'action' => 'added/subtracted',
                        'fromTo' => 'from/to',
                    ];

                    (new TransferReceivedStatusChangeMailService)->send($transfers, $data);

                    if (!empty($data['user']->formattedPhone)) {
                        (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                    }

                    if (isset($userInfo)) {
                        $data = [
                            'amount' => 'No Amount',
                            'user'   => $userInfo,
                            'action' => 'added/subtracted',
                            'fromTo' => 'from/to',
                        ];
    
                        (new TransferReceivedStatusChangeMailService)->send($transfers, $data);
    
                        if (!empty($data['user']->formattedPhone)) {
                            (new TransferReceivedStatusChangeSmsService)->send($transfers, $data);
                        }
                    }
                    
                    $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transfer')]));
                    return redirect(config('adminPrefix').'/transfers');
                }
            }
        }
    }
}
