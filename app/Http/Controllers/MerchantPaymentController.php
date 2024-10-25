<?php

namespace App\Http\Controllers;

use Exception, DB;
use App\Services\MerchantPaymentService;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{
    CurrencyPaymentMethod,
    PaymentMethod,
    Merchant,
};
use App\Services\Mail\MerchantPayment\{
    NotifyMerchantOnPaymentMailService,
    NotifyAdminOnPaymentMailService
};

class MerchantPaymentController extends Controller
{
    protected $helper, $merchantService;

    public function __construct()
    {
        $this->helper = new Common();
        $this->merchantService = new MerchantPaymentService();
    }

    public function index(Request $request)
    {
        $merchantId = $request->merchant_id;
        $merchantUuid = $request->merchant;
        $merchantCurrencyId = $request->currency_id;

        $data['merchant'] = $merchant = Merchant::with(['currency:id,code', 'user:id,status'])
            ->where(['id' => $merchantId, 'merchant_uuid' => $merchantUuid, 'currency_id' => $merchantCurrencyId])
            ->first(['id', 'user_id', 'currency_id', 'status']);

        if (!$merchant || $merchant->status != 'Approved') {
            $this->helper->one_time_message('error', __('Merchant not found!'));
            return redirect('payment/fail');
        }

        setPaymentData($request->all());


        //Check whether merchant is suspended
        $checkStandardMerchantUser = $this->helper->getUserStatus($merchant?->user?->status);

        if ($checkStandardMerchantUser == 'Suspended') {
            $data['message'] = __('Merchant is suspended!');
            return view('merchantPayment.user_suspended', $data);
        }

        //Check whether merchant is Inactive
        if ($checkStandardMerchantUser == 'Inactive') {
            $data['message'] = __('Merchant is inactive!');
            return view('merchantPayment.user_inactive', $data);
        }

        //For showing the message that merchant available or not
        $data['isMerchantAvailable'] = true;
        $data['paymentInfo'] = $request->all();

        $data['payment_methods'] = PaymentMethod::whereStatus('Active')->get(['id', 'name'])->toArray();

        $cpmWithoutMts = CurrencyPaymentMethod::where(['currency_id' => $merchant?->currency?->id])
            ->where('activated_for', 'like', "%deposit%")->pluck('method_id')->toArray();

        $paymoney = PaymentMethod::whereName('Mts')->first(['id']);
        array_push($cpmWithoutMts, $paymoney->id);
        $data['cpm'] = $cpmWithoutMts;

        return view('merchantPayment.index', $data);
    }

    public function showPaymentForm(Request $request)
    {
        $data = getPaymentData();
        $merchantCurrencyId = $request->currency_id;
        $currency = $this->helper->getCurrencyObject(['id' => $merchantCurrencyId], ['symbol', 'code', 'type']);
        $paymentMethod = PaymentMethod::whereName($request->method)->first(['id', 'name']);
        $methodId = $paymentMethod['id'];
        $totalAmount = $request->amount;

        $merchantCheck = Merchant::with('merchant_group:id,fee_bearer')->find($data['merchant_id'], ['id', 'user_id', 'status', 'fee', 'merchant_group_id']);

        if (optional($merchantCheck->merchant_group)->fee_bearer == 'User') {
            $feesLimit = $this->merchantService->checkMerchantPaymentFeesLimit($merchantCurrencyId, $methodId, $request->amount, $merchantCheck->fee);
            $totalAmount = $feesLimit['totalFee'] + $request->amount;
        }



        $paymentData = [
            'currency_id' =>  $merchantCurrencyId,
            'currencySymbol' => $currency->symbol,
            'currencyCode' => $currency->code,
            'currencyType' => $currency->type,
            'total' => $totalAmount,
            'totalAmount' => $totalAmount,
            'transaction_type' => Payment_Sent,
            'payment_type' => 'deposit',
            'payment_method' =>  $methodId,
            'redirectUrl' => route('gateway.payment.success'),
            'cancel_url' => url('payment/fail'),
            'gateway' => strtolower($request->method),
            'uuid' => unique_code()
        ];

        $paymentData = array_merge($data, $paymentData);

        return redirect(gatewayPaymentUrl($paymentData));
    }

    public function paymentSuccess(Request $request)
    {
        try {

            DB::beginTransaction();

            $data = getPaymentParam(request()->params);

            isGatewayValidMethod($data['payment_method']);

            $sender = isset($request->user) ? $request->user : null;

            $amount            = $data['amount'];
            $merchant          = $data['merchant_id'];
            $item_name         = $data['item_name'];
            $order_no          = $data['order'];
            $currencyId        = $data['currency_id'];
            $payment_method_id = $data['payment_method'];
            $uniqueCode        = $data['uuid'];

            $request->merge(['amount' => $amount, 'merchant' => $merchant, 'order_no' => $order_no, 'item_name' => $item_name]);

            $merchantCheck = Merchant::with('merchant_group:id,fee_bearer')->find($merchant, ['id', 'user_id', 'status', 'fee', 'merchant_group_id']);

            if (!$merchantCheck || $merchantCheck->status != 'Approved') {
                throw new Exception(__('Merchant not found!'));
            }

            $successPaymentMethods = [Stripe, Paypal, Mts];

            $status = in_array($payment_method_id, $successPaymentMethods) ? 'Success' : 'Pending';

            //Deposit + Merchant Fee
            $feesLimit = $this->merchantService->checkMerchantPaymentFeesLimit($currencyId, $payment_method_id, $amount, $merchantCheck->fee);

            //Merchant payment
            $merchantPayment = $this->merchantService->makeMerchantPayment($request, $merchantCheck, $feesLimit, $currencyId, $uniqueCode, $uniqueCode, $payment_method_id, $status);

            //Merchant Transaction
            $transaction = $this->merchantService->makeMerchantTransaction($request, $merchantCheck, $feesLimit,  $currencyId, $uniqueCode, $merchantPayment, $payment_method_id, $status);


            if (!is_null($sender)) {

                if ($merchantCheck->user_id == $sender) {
                    throw new Exception(__('Merchant cannot make payment to himself!'));
                }

                $this->merchantService->makeUserTransaction($request, $merchantCheck, $feesLimit,  $currencyId, $uniqueCode, $merchantPayment, $status);

                $senderWallet = $this->helper->getUserWallet([], ['user_id' => $sender, 'currency_id' =>  $currencyId], ['id', 'balance']);

                $this->merchantService->updateSenderWallet($request->amount, $merchantCheck, $senderWallet);
            }

            if ($status == 'Success') {

                $merchantWallet = $this->helper->getUserWallet([], ['user_id' => $merchantCheck->user_id, 'currency_id' =>  $currencyId], ['id', 'balance']);

                $this->merchantService->createOrUpdateMerchantWallet($request->amount, $merchantCheck, $currencyId, $feesLimit['totalFee'], $merchantWallet);
            }

            DB::commit();

            // Send mail to admin
            (new NotifyAdminOnPaymentMailService())->send($merchantPayment, ['type' => 'payment', 'medium' => 'email', 'fee_bearer' => $merchantCheck?->merchant_group?->fee_bearer, 'fee' => $feesLimit['totalFee']]);

            // Send mail to merchant
            (new NotifyMerchantOnPaymentMailService())->send($merchantPayment, ['fee_bearer' => $merchantCheck?->merchant_group?->fee_bearer, 'fee' => $feesLimit['totalFee']]);

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return $transaction->id;
            }

            setPaymentData($data);
            return redirect()->route('merchant.payment.success');

        } catch (Exception $e) {
            DB::rollback();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('payment/fail');
        }
    }

    public function fail()
    {
        return view('merchantPayment.fail');
    }

    public function success()
    {
        try {
            $data =  getPaymentData('forget');
            return view('merchantPayment.success', $data);
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('payment/fail');
        }
    }
}
